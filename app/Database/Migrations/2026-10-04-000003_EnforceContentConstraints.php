<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnforceContentConstraints extends Migration
{
    public function up()
    {
        foreach (['review' => 'review_owner_destination_unique', 'wishlist' => 'wishlist_owner_destination_unique'] as $table => $name) {
            $duplicates = $this->db->table($table)->select('user_id, wisata_id')->groupBy('user_id, wisata_id')->having('COUNT(*) >', 1, false)->get()->getNumRows();
            if ($duplicates) throw new \RuntimeException('Ada duplikasi pada tabel ' . $table . '. Periksa dan rekonsiliasi data sebelum menambahkan constraint; tidak ada data yang dihapus otomatis.');
            if (!isset($this->db->getIndexData($table)[$name])) {
                $this->forge->addUniqueKey(['user_id', 'wisata_id'], $name);
                $this->forge->processIndexes($table);
            }
        }
        if ($this->db->DBDriver === 'MySQLi') {
            $this->forge->modifyColumn('users', ['email' => ['type' => 'VARCHAR', 'constraint' => 254, 'null' => false]]);
            $this->forge->modifyColumn('berita', ['judul' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false]]);
        }
    }
    public function down()
    {
        throw new \RuntimeException('Constraint mempertahankan konsistensi data. Gunakan pemulihan backup yang diperiksa.');
    }
}