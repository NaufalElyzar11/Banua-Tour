<?php

use Tests\Support\SafetyTestCase;
use App\Libraries\BookingService;
use App\Models\BookingModel;
use App\Models\UserModel;
use App\Models\WisataModel;

final class BookingSafetyTest extends SafetyTestCase
{
    public function testReservationRecalculatesPriceAndDeduplicatesToken(): void
    {
        $service = new BookingService($this->db); $destination = (new WisataModel())->find(901); $token = str_repeat('a', 64);
        $id = $service->reserve(1, $destination, date('Y-m-d'), '3', $token);
        $this->assertSame($id, $service->reserve(1, $destination, date('Y-m-d'), '3', $token));
        $booking = (new BookingModel())->find($id);
        $this->assertEquals(37500, $booking['total_harga']);
        $this->assertSame('upcoming', $booking['status']); $this->assertSame('unpaid', $booking['status_pembayaran']);
        $this->assertNull($booking['kode_tiket']);
        $this->assertSame(1, (new BookingModel())->countAllResults());
        $this->expectException(DomainException::class);
        $service->reserve(1, $destination, date('Y-m-d'), '4', $token);
    }

    public function testInvalidDatesAndQuantitiesCannotCreateBookings(): void
    {
        $service = new BookingService($this->db); $destination = (new WisataModel())->find(901);
        foreach ([['2026-02-30','1'], [date('Y-m-d', strtotime('-1 day')),'1'], [date('Y-m-d', strtotime('+2 years')),'1'], [date('Y-m-d'),'0'], [date('Y-m-d'),'101'], [date('Y-m-d'),'1.5'], [date('Y-m-d'),'-2']] as [$date,$qty]) {
            try { $service->reserve(1, $destination, $date, $qty, bin2hex(random_bytes(32))); $this->fail('Invalid booking was accepted.'); }
            catch (DomainException $e) { $this->assertNotEmpty($e->getMessage()); }
        }
        $this->assertSame(0, (new BookingModel())->countAllResults());
    }

    public function testPaymentAndVisitAreRecordedOnce(): void
    {
        $service = new BookingService($this->db); $id = $this->reservation();
        $service->confirmPayment($id, 3, 'TRANSFER-123');
        $paid = (new BookingModel())->find($id);
        $this->assertSame('paid', $paid['status_pembayaran']); $this->assertMatchesRegularExpression('/\ABT-[A-F0-9]{32}\z/', $paid['kode_tiket']);
        try { $service->confirmPayment($id, 3, 'DUPLICATE'); $this->fail('Payment replay accepted.'); } catch (DomainException $e) { $this->assertNotEmpty($e->getMessage()); }
        $service->complete($id, 3);
        $this->assertSame('completed', (new BookingModel())->find($id)['status']);
        $this->assertSame(2, $this->db->table('booking_events')->where('booking_id', $id)->countAllResults());
        $this->expectException(DomainException::class); $service->complete($id, 3);
    }

    public function testFutureVisitCannotBeCompletedAndPaidBookingCannotBeCanceled(): void
    {
        $service = new BookingService($this->db); $id = $this->reservation(date('Y-m-d', strtotime('+1 day')));
        $service->confirmPayment($id, 3, 'TRANSFER-456');
        foreach (['complete', 'cancel'] as $method) {
            try { $service->$method($id, $method === 'cancel' ? 1 : 3); $this->fail('Invalid transition accepted.'); }
            catch (DomainException $e) { $this->assertNotEmpty($e->getMessage()); }
        }
        $this->assertSame('upcoming', (new BookingModel())->find($id)['status']);
        $this->assertSame(1, $this->db->table('booking_events')->countAllResults());
    }

    public function testAnotherUserCannotCancelBooking(): void
    {
        $id = $this->reservation();
        try { (new BookingService($this->db))->cancel($id, 2); $this->fail('Another user canceled booking.'); }
        catch (DomainException $e) { $this->assertSame('upcoming', (new BookingModel())->find($id)['status']); }
        (new BookingService($this->db))->cancel($id, 1);
        $this->assertSame('canceled', (new BookingModel())->find($id)['status']);
    }

    public function testEventFailureRollsBackPayment(): void
    {
        $id = $this->reservation(); $this->db->query('DROP TABLE db_booking_events');
        try { (new BookingService($this->db))->confirmPayment($id, 3, 'TRANSFER-ROLLBACK'); $this->fail('Missing audit table accepted.'); }
        catch (Throwable $e) { $this->assertSame('unpaid', (new BookingModel())->find($id)['status_pembayaran']); }
    }

    public function testUserPasswordIsHashedExactlyOnceAndCoordinatesPersist(): void
    {
        $model = new UserModel();
        $id = $model->insert(['nama'=>'New Tester','username'=>'newtester','email'=>'new@example.test','password'=>'long-test-passphrase','daerah'=>'Belum diatur','role'=>'user']);
        $this->assertTrue(password_verify('long-test-passphrase', $model->find($id)['password']));
        $model->update($id, ['password'=>'replacement-passphrase']);
        $this->assertTrue(password_verify('replacement-passphrase', $model->find($id)['password']));
        $destination = new WisataModel(); $destination->update(901, ['latitude'=>-3.5,'longitude'=>114.6]);
        $this->assertEquals(-3.5, $destination->find(901)['latitude']); $this->assertEquals(114.6, $destination->find(901)['longitude']);
    }
}