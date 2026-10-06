<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Upgrade extends BaseCommand
{
    protected $group = 'BanuaTour';
    protected $name = 'app:upgrade';
    protected $description = 'Backup database lokal lalu jalankan migrasi BanuaTour.';

    public function run(array $params)
    {
        $db = db_connect();
        $db->initialize();
        if ($db->DBDriver !== 'MySQLi') {
            throw new \RuntimeException('Perintah upgrade ini memerlukan MySQL; pengujian SQLite memakai fixture terpisah.');
        }
        $folder = WRITEPATH . 'backups';
        if (!is_dir($folder) && !mkdir($folder, 0700, true)) {
            throw new \RuntimeException('Folder backup tidak dapat dibuat.');
        }
        $path = $folder . '/banuatour-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.sql';
        $binary = env('MYSQLDUMP_PATH', PHP_OS_FAMILY === 'Windows'
            ? 'C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqldump.exe' : 'mysqldump');
        $credentials = tempnam($folder, 'backup-client-');
        if ($credentials === false) throw new \RuntimeException('File konfigurasi backup tidak dapat dibuat.');
        $quote = static fn ($value) => '"' . str_replace(['\\', '"', "\r", "\n"], ['\\\\', '\\"', '\\r', '\\n'], (string) $value) . '"';
        try {
            chmod($credentials, 0600);
            if (file_put_contents($credentials, "[client]\nuser=" . $quote($db->username) . "\npassword=" . $quote($db->password)
                . "\nhost=" . $quote($db->hostname) . "\nport=" . (int) $db->port . "\n") === false) throw new \RuntimeException('Konfigurasi backup belum tersimpan.');
            $process = proc_open([
                $binary, '--defaults-extra-file=' . $credentials, '--single-transaction', '--no-tablespaces',
                '--hex-blob', '--set-gtid-purged=OFF', '--column-statistics=0', $db->database,
            ], [0 => ['pipe', 'r'], 1 => ['file', $path, 'w'], 2 => ['pipe', 'w']], $pipes, null, null, ['bypass_shell' => true]);
            if (!is_resource($process)) {
                throw new \RuntimeException('mysqldump tidak dapat dijalankan. Atur MYSQLDUMP_PATH di .env.');
            }
            fclose($pipes[0]);
            stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            if (proc_close($process) !== 0 || !is_file($path) || filesize($path) < 100) {
                throw new \RuntimeException('Backup belum berhasil. Periksa akses mysqldump; migrasi dibatalkan.');
            }
        } finally {
            if (is_file($credentials)) {
                unlink($credentials);
            }
        }
        chmod($path, 0600);
        CLI::write('Backup tersimpan di writable/backups/' . basename($path), 'green');
        if (!service('migrations')->setNamespace('App')->latest()) {
            throw new \RuntimeException('Migrasi belum berhasil. Periksa log; backup tetap tersedia.');
        }
        CLI::write('Database BanuaTour berhasil diperbarui.', 'green');
    }
}
