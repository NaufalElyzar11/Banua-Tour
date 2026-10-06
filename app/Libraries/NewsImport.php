<?php
namespace App\Libraries;

use DomainException;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class NewsImport
{
    public function read(string $path, array $destinationIds): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) throw new DomainException('File harus berupa XLSX yang valid.');
        try {
            $bytes = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->statIndex($i);
                $bytes += $entry['size'];
                if ($bytes > 20 * 1024 * 1024 || $zip->numFiles > 1000) throw new DomainException('Isi XLSX terlalu besar. Maksimal 20 MB setelah dibuka.');
            }
        } finally { $zip->close(); }
        $reader = new Xlsx();
        $reader->setReadDataOnly(true);
        $reader->setReadFilter(new class implements IReadFilter {
            public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
            { return $row <= 2001 && in_array($columnAddress, ['A','B','C','D','E','F'], true); }
        });
        $info = $reader->listWorksheetInfo($path);
        if (!$info || $info[0]['totalRows'] > 2001) throw new DomainException('Maksimal 2.000 baris berita per impor.');
        $reader->setLoadSheetsOnly($info[0]['worksheetName']);
        $book = $reader->load($path);
        try {
            $rows = $book->getSheet(0)->toArray(null, false, false, false);
            $items = [];
            foreach (array_slice($rows, 1) as $offset => $row) {
                if (!array_filter($row, static fn ($value) => $value !== null && $value !== '')) continue;
                foreach ($row as $value) if (is_string($value) && str_starts_with($value, '=')) throw new DomainException('Rumus tidak diizinkan pada baris ' . ($offset + 2) . '.');
                $title = trim((string) ($row[0] ?? '')); $content = trim((string) ($row[1] ?? ''));
                $id = trim((string) ($row[2] ?? '')); $link = trim((string) ($row[3] ?? '')); $image = trim((string) ($row[4] ?? '')); $date = trim((string) ($row[5] ?? ''));
                if (mb_strlen($title) < 5 || mb_strlen($title) > 255 || mb_strlen($content) < 10 || mb_strlen($content) > 50000
                    || ($id !== '' && !in_array($id, array_map('strval', $destinationIds), true))
                    || ($link !== '' && (!safe_url($link) || strlen($link) > 255))
                    || ($image !== '' && (!safe_url($image) || strlen($image) > 255))
                    || ($date !== '' && (!preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $date) || !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))))) {
                    throw new DomainException('Data pada baris ' . ($offset + 2) . ' tidak valid. Periksa judul, konten, ID wisata, URL, dan tanggal YYYY-MM-DD.');
                }
                $items[] = ['judul' => $title, 'konten' => $content, 'wisata_id' => $id === '' ? null : (int) $id, 'link_berita' => $link ?: null, 'gambar' => $image ?: null, 'tanggal_post' => $date ?: date('Y-m-d'), 'status' => 'draft'];
            }
            if (!$items) throw new DomainException('Tidak ada berita untuk diimpor.');
            return $items;
        } finally { $book->disconnectWorksheets(); }
    }
}