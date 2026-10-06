<?php
namespace Tests\Support;

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\UserModel;

abstract class SafetyTestCase extends CIUnitTestCase
{
    protected function setUp(): void
    {
        $this->resetServices();
        $this->app = null;
        parent::setUp();
        helper(['form', 'site']);
        $this->db = db_connect('tests');
        if ($this->db->DBDriver !== 'SQLite3' || $this->db->database !== ':memory:') throw new \RuntimeException('Tests require isolated SQLite memory database.');
        foreach (['booking_events','bookings','review','wishlist','minat_user','berita','wisata','kategori','users'] as $table) $this->db->query('DROP TABLE IF EXISTS db_' . $table);
        $schema = [
            'users' => 'user_id INTEGER PRIMARY KEY AUTOINCREMENT, nama TEXT NOT NULL, username TEXT NOT NULL UNIQUE COLLATE NOCASE, email TEXT NOT NULL UNIQUE COLLATE NOCASE, password TEXT NOT NULL, daerah TEXT NOT NULL, jenis_kelamin TEXT, umur INTEGER, role TEXT NOT NULL DEFAULT "user", created_at TEXT DEFAULT CURRENT_TIMESTAMP',
            'kategori' => 'kategori_id INTEGER PRIMARY KEY, nama_kategori TEXT NOT NULL',
            'wisata' => 'wisata_id INTEGER PRIMARY KEY AUTOINCREMENT, nama TEXT NOT NULL, daerah TEXT NOT NULL, deskripsi TEXT NOT NULL, harga NUMERIC NOT NULL, kategori_id INTEGER, gambar_wisata TEXT, link_video TEXT, latitude NUMERIC, longitude NUMERIC, created_at TEXT DEFAULT CURRENT_TIMESTAMP',
            'bookings' => 'booking_id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, wisata_id INTEGER NOT NULL, tanggal_kunjungan TEXT NOT NULL, jumlah_orang INTEGER NOT NULL, total_harga NUMERIC NOT NULL, status TEXT NOT NULL DEFAULT "upcoming", created_at TEXT, kode_tiket TEXT',
            'berita' => 'berita_id INTEGER PRIMARY KEY AUTOINCREMENT, judul TEXT, konten TEXT, wisata_id INTEGER, link_berita TEXT, gambar TEXT, tanggal_post TEXT',
            'review' => 'review_id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, wisata_id INTEGER, rating INTEGER, komentar TEXT, tanggal_review TEXT',
            'wishlist' => 'wishlist_id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, wisata_id INTEGER, tanggal_ditambahkan TEXT DEFAULT CURRENT_TIMESTAMP',
            'minat_user' => 'user_id INTEGER, kategori_id INTEGER',
        ];
        foreach ($schema as $table => $fields) $this->db->query('CREATE TABLE db_' . $table . ' (' . $fields . ')');
        $this->db->resetDataCache();
        $this->db->resetTransStatus();
        require_once APPPATH . 'Database/Migrations/2026-10-04-000001_HardenBookingsAndDestinationInfo.php';
        (new \App\Database\Migrations\HardenBookingsAndDestinationInfo())->up();
        require_once APPPATH . 'Database/Migrations/2026-10-04-000003_EnforceContentConstraints.php';
        (new \App\Database\Migrations\EnforceContentConstraints())->up();
        $this->db->table('kategori')->insertBatch([['kategori_id' => 1, 'nama_kategori' => 'Alam'], ['kategori_id' => 2, 'nama_kategori' => 'Budaya']]);
        static $hash;
        $hash ??= password_hash('testing-passphrase-2026', PASSWORD_ARGON2ID);
        foreach ([1 => 'user', 2 => 'user', 3 => 'admin'] as $id => $role) $this->db->table('users')->insert(['user_id' => $id, 'nama' => 'Tester ' . $id, 'username' => 'tester' . $id, 'email' => 'tester' . $id . '@example.test', 'password' => $hash, 'daerah' => 'Banjar', 'role' => $role]);
        $this->db->table('wisata')->insertBatch([
            ['wisata_id' => 901, 'nama' => 'Bukit Aman', 'daerah' => 'Banjar', 'deskripsi' => 'Destinasi uji untuk pengunjung.', 'harga' => 12500, 'kategori_id' => 1],
            ['wisata_id' => 902, 'nama' => 'Museum Banjar', 'daerah' => 'Banjar', 'deskripsi' => 'Museum untuk pengunjung.', 'harga' => 5000, 'kategori_id' => 2],
            ['wisata_id' => 903, 'nama' => 'Bukit Kota', 'daerah' => 'Banjarmasin', 'deskripsi' => 'Destinasi di kota.', 'harga' => 10000, 'kategori_id' => 1],
        ]);
    }

    protected function loginSession(int $id = 1): array
    {
        $user = (new UserModel())->find($id);
        return ['isLoggedIn' => true, 'user_id' => $id, 'role' => $user['role'], 'nama' => $user['nama']];
    }

    protected function reservation(string $date = '', int $user = 1): int
    {
        return (new \App\Libraries\BookingService($this->db))->reserve($user, (new \App\Models\WisataModel())->find(901), $date ?: date('Y-m-d'), '2', bin2hex(random_bytes(32)));
    }
}
