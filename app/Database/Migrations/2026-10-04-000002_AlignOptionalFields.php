<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlignOptionalFields extends Migration
{
    public function up()
    {
        if ($this->db->DBDriver === 'MySQLi') {
            $this->forge->modifyColumn('wisata', [
                'latitude' => ['type' => 'DECIMAL', 'constraint' => '10,8', 'null' => true],
                'longitude' => ['type' => 'DECIMAL', 'constraint' => '11,8', 'null' => true],
            ]);
            $this->forge->modifyColumn('berita', ['judul' => ['type' => 'VARCHAR', 'constraint' => 255]]);
        }
    }
    public function down()
    {
        throw new \RuntimeException('Kolom opsional mempertahankan data. Pulihkan dari backup yang sudah diperiksa jika diperlukan.');
    }
}