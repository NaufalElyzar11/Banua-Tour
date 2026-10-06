<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class HardenBookingsAndDestinationInfo extends Migration
{
    public function up()
    {
        foreach (['users', 'wisata', 'bookings'] as $table) {
            if (!$this->db->tableExists($table)) {
                throw new \RuntimeException('Import database BanuaTour terlebih dahulu. Tabel ' . $table . ' belum tersedia.');
            }
        }
        $fields = [
            'status_pembayaran' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'unpaid'],
            'payment_reference' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'paid_at' => ['type' => 'DATETIME', 'null' => true],
            'confirmed_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'request_token' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'hidden_by_user' => ['type' => 'BOOLEAN', 'default' => false],
            'archived_by_admin' => ['type' => 'BOOLEAN', 'default' => false],
        ];
        foreach ($fields as $name => $definition) {
            if (!$this->db->fieldExists($name, 'bookings')) {
                $this->forge->addColumn('bookings', [$name => $definition]);
            }
        }
        if (!$this->db->fieldExists('kode_tiket', 'bookings')) {
            $this->forge->addColumn('bookings', ['kode_tiket' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true]]);
        }
        $indexes = $this->db->getIndexData('bookings');
        if (!isset($indexes['bookings_request_token_unique'])) {
            $this->forge->addUniqueKey('request_token', 'bookings_request_token_unique');
            $this->forge->processIndexes('bookings');
        }
        foreach (['jam_buka', 'fasilitas', 'akses_transportasi', 'aksesibilitas', 'ketentuan_tiket', 'kontak_pengelola'] as $field) {
            if (!$this->db->fieldExists($field, 'wisata')) {
                $this->forge->addColumn('wisata', [$field => ['type' => 'TEXT', 'null' => true]]);
            }
        }
        // Demographics are optional; do not invent a gender or age for new accounts.
        if ($this->db->tableExists('berita')) {
            foreach ([
                'status' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'published'],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ] as $name => $definition) {
                if (!$this->db->fieldExists($name, 'berita')) {
                    $this->forge->addColumn('berita', [$name => $definition]);
                }
            }
        }
        if ($this->db->DBDriver === 'MySQLi') {
            $this->forge->modifyColumn('users', [
                'jenis_kelamin' => ['type' => 'ENUM', 'constraint' => ['L', 'P'], 'null' => true],
                'umur' => ['type' => 'INT', 'null' => true],
                'email' => ['type' => 'VARCHAR', 'constraint' => 254],
            ]);
        }
        if (!$this->db->tableExists('booking_events')) {
            $this->forge->addField([
                'event_id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'booking_id' => ['type' => 'INT', 'unsigned' => true],
                'actor_id' => ['type' => 'INT', 'unsigned' => true],
                'event' => ['type' => 'VARCHAR', 'constraint' => 40],
                'reference' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'created_at' => ['type' => 'DATETIME'],
            ]);
            $this->forge->addKey('event_id', true);
            $this->forge->addKey('booking_id');
            $this->forge->createTable('booking_events');
        }
    }

    public function down()
    {
        // Audit and payment data must survive a rollback. A restore requires a reviewed backup.
        throw new \RuntimeException('Migrasi ini mempertahankan data transaksi. Gunakan backup yang sudah diverifikasi untuk pemulihan.');
    }
}
