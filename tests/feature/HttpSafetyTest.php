<?php

use Tests\Support\SafetyTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Models\BookingModel;
use App\Models\UserModel;

final class HttpSafetyTest extends SafetyTestCase
{
    use FeatureTestTrait;

    private function request(string $method, string $path, array $post = [], array $session = [], bool $csrf = true)
    {
        $this->app = $this->createApplication();
        \Config\Services::resetSingle('response');
        \Config\Services::resetSingle('redirectresponse');
        \Config\Services::resetSingle('security');
        \Config\Services::resetSingle('renderer');
        $token = bin2hex(random_bytes(16));
        $session[config('Security')->tokenName] = $token;
        if ($method === 'POST' && $csrf) $post[config('Security')->tokenName] = $token;
        $this->withSession($session)->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json']);
        return $this->call($method, $path, $post);
    }

    public function testMissingCsrfRejectsMutation(): void
    {
        try { $this->request('POST', 'wishlist/add/901', [], $this->loginSession(), false); $this->fail('Missing CSRF accepted.'); }
        catch (\CodeIgniter\Security\Exceptions\SecurityException $e) { $this->assertNotEmpty($e->getMessage()); }
        $this->assertSame(0, $this->db->table('wishlist')->countAllResults());
    }

    public function testGetCannotMutateAndGuestAjaxIsUnauthorized(): void
    {
        try { $this->request('GET', 'wishlist/add/901', [], $this->loginSession()); $this->fail('GET mutation accepted.'); }
        catch (\CodeIgniter\Exceptions\PageNotFoundException $e) { $this->assertNotEmpty($e->getMessage()); }
        $this->request('POST', 'wishlist/add/901')->assertStatus(401);
        $this->assertSame(0, $this->db->table('wishlist')->countAllResults());
    }

    public function testRoleIsCheckedAgainstDatabase(): void
    {
        $session = $this->loginSession(); $session['role'] = 'admin';
        $this->request('GET', 'admin/booking', [], $session)->assertStatus(403);
        (new UserModel())->delete(1);
        $this->request('GET', 'riwayat', [], $session)->assertStatus(401);
    }

    public function testTicketOwnershipPaymentAndReadOnlyBehavior(): void
    {
        $id = $this->reservation();
        $this->request('GET', 'riwayat/tiket/' . $id, [], $this->loginSession(2))->assertStatus(404);
        $this->request('GET', 'riwayat/tiket/' . $id, [], $this->loginSession())->assertStatus(409);
        $this->assertNull((new BookingModel())->find($id)['kode_tiket']);
        (new \App\Libraries\BookingService($this->db))->confirmPayment($id, 3, 'TRANSFER-HTTP');
        $result = $this->request('GET', 'riwayat/tiket/' . $id, [], $this->loginSession());
        $result->assertStatus(200); $this->assertStringContainsString('no-store', $result->response()->getHeaderLine('Cache-Control'));
        $this->assertSame((new BookingModel())->find($id)['kode_tiket'], json_decode($result->getJSON(), true)['data']['kode_tiket']);
    }

    public function testArchiveAndRestorePreserveBookingAndRejectOtherOwner(): void
    {
        $id = $this->reservation(); (new \App\Libraries\BookingService($this->db))->cancel($id, 1);
        $this->request('POST', 'riwayat/delete/' . $id, [], $this->loginSession(2))->assertStatus(404);
        $this->request('POST', 'riwayat/delete/' . $id, [], $this->loginSession())->assertStatus(200);
        $this->assertEquals(1, (new BookingModel())->find($id)['hidden_by_user']);
        $this->request('POST', 'riwayat/restore/' . $id, [], $this->loginSession())->assertStatus(200);
        $this->assertEquals(0, (new BookingModel())->find($id)['hidden_by_user']);
    }

    public function testBookingPostIgnoresClientPriceAndReplaysSafely(): void
    {
        $token = str_repeat('b', 64); $session = $this->loginSession();
        $session['booking_tokens'] = [$token => ['wisata_id' => 901, 'expires' => time() + 300]];
        $post = ['wisata_id' => '901', 'tanggal_kunjungan' => date('Y-m-d'), 'jumlah_orang' => '2', 'booking_token' => $token, 'total_harga' => '1', 'status_pembayaran' => 'paid', 'status' => 'completed'];
        $this->request('POST', 'booking/store', $post, $session)->assertRedirectTo(base_url('riwayat'));
        $this->request('POST', 'booking/store', $post, $session)->assertRedirectTo(base_url('riwayat'));
        $booking = (new BookingModel())->first();
        $this->assertEquals(25000, $booking['total_harga']); $this->assertSame('unpaid', $booking['status_pembayaran']);
        $this->assertSame('upcoming', $booking['status']); $this->assertSame(1, (new BookingModel())->countAllResults());
    }

    public function testSearchEscapesPayloadAndCombinesFilters(): void
    {
        $payload = '<img src=x onerror=alert(1)>';
        $result = $this->request('GET', 'destinasi/search?keyword=' . rawurlencode($payload));
        $result->assertStatus(200);
        $this->assertStringNotContainsString($payload, $result->response()->getBody());
        $this->assertStringContainsString('&lt;img', $result->response()->getBody());
        $result = $this->request('GET', 'destinasi?keyword=Banjar&kategori=1&daerah=Banjar');
        $result->assertStatus(200);
        $this->assertStringContainsString('Bukit Aman', $result->response()->getBody());
        $this->assertStringNotContainsString('Museum Banjar', $result->response()->getBody());
        $this->assertStringNotContainsString('Bukit Kota', $result->response()->getBody());
    }

    public function testRegistrationCannotEscalateRoleAndDoesNotStorePasswordInOldInput(): void
    {
        $this->request('POST', 'auth/doRegister', ['nama'=>'Visitor','username'=>'visitor','email'=>'visitor@example.test','password'=>'a-good-long-passphrase','confirm_password'=>'a-good-long-passphrase','role'=>'admin'])->assertRedirectTo(base_url('auth/login'));
        $user = (new UserModel())->where('username','visitor')->first();
        $this->assertSame('user', $user['role']); $this->assertNull($user['jenis_kelamin']); $this->assertTrue(password_verify('a-good-long-passphrase', $user['password']));
        $this->request('POST', 'auth/doRegister', ['nama'=>'Visitor','username'=>'different','email'=>'different@example.test','password'=>'short','confirm_password'=>'short'])->assertRedirect();
        $old = session()->getFlashdata('_ci_old_input');
        $this->assertArrayNotHasKey('password', $old['post']); $this->assertArrayNotHasKey('confirm_password', $old['post']);
    }

    public function testLoginAndReturnPathPreserved(): void
    {
        $this->request('POST', 'auth/doLogin', ['email'=>'tester1','password'=>'testing-passphrase-2026'], ['return_to'=>'booking/pembelian/901'])->assertRedirectTo(base_url('booking/pembelian/901'));
        $this->assertTrue(session('isLoggedIn')); $this->assertEquals(1, session('user_id'));
    }

    public function testUnvisitedUserCannotReviewAndRatingMustBeInteger(): void
    {
        $this->request('POST', 'destinasi/addReview', ['wisata_id'=>'901','rating'=>'5','komentar'=>'Pengalaman sangat menyenangkan.'], $this->loginSession())->assertStatus(403);
        $id = $this->reservation(); $service = new \App\Libraries\BookingService($this->db); $service->confirmPayment($id,3,'TRANSFER-REVIEW'); $service->complete($id,3);
        $this->request('POST', 'destinasi/addReview', ['wisata_id'=>'901','rating'=>'4.5','komentar'=>'Pengalaman sangat menyenangkan.'], $this->loginSession())->assertStatus(422);
        $this->request('POST', 'destinasi/addReview', ['wisata_id'=>'901','rating'=>'5','komentar'=>'Pengalaman sangat menyenangkan.'], $this->loginSession())->assertStatus(200);
        $this->request('POST', 'destinasi/addReview', ['wisata_id'=>'901','rating'=>'5','komentar'=>'Pengalaman sangat menyenangkan.'], $this->loginSession())->assertStatus(409);
    }

    public function testProfileRejectsDuplicateEmailAndInvalidPreferenceWithoutDeletingOldChoice(): void
    {
        $this->request('POST', 'profile/update', ['nama'=>'Visitor','username'=>'tester1','email'=>'tester2@example.test','daerah'=>''], $this->loginSession())->assertRedirect();
        $this->assertSame('tester1@example.test', (new UserModel())->find(1)['email']);
        $this->db->table('minat_user')->insert(['user_id'=>1,'kategori_id'=>1]);
        $this->request('POST','profile/updatePreferences',['kategori_ids'=>['999']],$this->loginSession())->assertRedirect();
        $this->assertSame(1, $this->db->table('minat_user')->where('user_id',1)->countAllResults());
    }

    public function testPagesRenderWithNewSchemaAndEmptyStates(): void
    {
        $id = $this->reservation();
        $this->db->table('berita')->insert(['judul'=>'Berita terbaru','konten'=>'Konten wisata untuk pengunjung.','tanggal_post'=>date('Y-m-d'),'status'=>'published','gambar'=>null]);
        foreach (['/', 'destinasi/detail/901', 'profile', 'wishlist', 'riwayat', 'booking/pembelian/901', 'admin/dashboard', 'admin/booking', 'admin/wisata/create', 'admin/wisata/edit/901', 'admin/users', 'admin/users/edit/1', 'admin/review', 'admin/berita', 'admin/berita/create', 'admin/berita/edit/1'] as $path) {
            $this->request('GET', $path, [], $this->loginSession(3))->assertStatus(200);
        }
    }

    public function testLoginRateLimitRejectsRepeatedFailures(): void
    {
        for ($i = 0; $i < 10; $i++) $this->request('POST','auth/doLogin',['email'=>'unknown@example.test','password'=>'wrong-password'])->assertRedirect();
        $this->request('POST','auth/doLogin',['email'=>'unknown@example.test','password'=>'wrong-password'])->assertStatus(429);
        $this->assertFalse((bool) session('isLoggedIn'));
    }

    public function testPaginationReturnsOnlyTwelveAndPreservesFilters(): void
    {
        for ($i = 0; $i < 14; $i++) $this->db->table('wisata')->insert(['nama'=>'Destinasi ' . str_pad((string) $i,2,'0',STR_PAD_LEFT),'daerah'=>'Banjar','deskripsi'=>'Deskripsi untuk destinasi.','harga'=>1000,'kategori_id'=>1]);
        $result = $this->request('GET','destinasi?daerah=Banjar&kategori=1&sort=price-asc&page=2');
        $result->assertStatus(200);
        $body = $result->response()->getBody();
        $this->assertSame(3, substr_count($body, 'class="destination-card"'));
        $this->assertStringContainsString('daerah=Banjar', html_entity_decode($body));
        $this->assertStringContainsString('15 destinasi ditemukan', $body);
    }

    public function testAdminSavesDecimalPriceOptionalCoordinatesAndNews(): void
    {
        $admin = $this->loginSession(3);
        $this->request('POST','admin/wisata/update/901',['nama'=>'Bukit Aman','daerah'=>'Banjar','deskripsi'=>'Deskripsi wisata yang sudah diperiksa.','harga'=>'12500.50','kategori_id'=>'1','latitude'=>'','longitude'=>'','link_video'=>'https://youtu.be/DwZdUKfUvxw','jam_buka'=>'08.00–17.00'],$admin)->assertRedirectTo(base_url('admin/wisata'));
        $destination = (new \App\Models\WisataModel())->find(901);
        $this->assertEquals(12500.50, $destination['harga']); $this->assertNull($destination['latitude']);
        $this->assertSame('08.00–17.00', $destination['jam_buka']);
        $this->request('POST','admin/berita/store',['judul'=>'Informasi perjalanan Banua','konten'=>'Informasi wisata yang sudah diperiksa pengelola.','status'=>'draft','wisata_id'=>'901','link_berita'=>'https://example.test/article','gambar_url'=>'https://example.test/image.jpg'],$admin)->assertRedirectTo(base_url('admin/berita'));
        $this->assertSame('draft', (new \App\Models\BeritaModel())->first()['status']);
        $this->request('POST','admin/users/store',['nama'=>'Pengunjung baru','username'=>'admincreated','email'=>'admincreated@example.test','password'=>'a-long-new-passphrase','role'=>'user'],$admin)->assertRedirect();
        $this->assertTrue(password_verify('a-long-new-passphrase', (new UserModel())->where('username','admincreated')->first()['password']));
    }
}
