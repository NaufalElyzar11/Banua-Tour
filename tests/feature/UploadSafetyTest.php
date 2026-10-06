<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\Files\UploadedFile;
use App\Libraries\MediaStorage;
use App\Libraries\NewsImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

final class UploadSafetyTest extends CIUnitTestCase
{
    private array $temporary = [];
    protected function setUp(): void { parent::setUp(); helper('site'); }
    protected function tearDown(): void { foreach ($this->temporary as $file) if (is_file($file)) unlink($file); parent::tearDown(); }
    private function temporary(): string { $path = tempnam(sys_get_temp_dir(), 'banua-test-'); $this->temporary[] = $path; return $path; }
    private function uploaded(string $path, string $name): UploadedFile
    {
        // HTTP upload provenance is enforced by UploadedFile in production; emulate it only for isolated image tests.
        return new class($path, $name, 'image/jpeg', filesize($path), UPLOAD_ERR_OK) extends UploadedFile {
            public function isValid(): bool { return true; }
        };
    }
    public function testImageWithPhpNameAndTrailingCodeIsReencoded(): void
    {
        $path = $this->temporary(); $image = imagecreatetruecolor(2400, 10); imagejpeg($image, $path); imagedestroy($image);
        file_put_contents($path, '<?php echo "must-not-survive"; ?>', FILE_APPEND);
        $storage = new MediaStorage(); $name = $storage->saveImage($this->uploaded($path, 'photo.php'), 'berita');
        $this->temporary[] = FCPATH . 'uploads/berita/' . $name;
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{32}\.jpg\z/', $name);
        $this->assertSame(1920, getimagesize(end($this->temporary))[0]);
        $this->assertStringNotContainsString('must-not-survive', file_get_contents(end($this->temporary)));
        $this->assertFalse($storage->deleteImage('berita', '../index.php'));
        $this->assertTrue($storage->deleteImage('berita', $name));
    }
    public function testFakeImageIsRejected(): void
    {
        $path = $this->temporary(); file_put_contents($path, '<?php echo "not-an-image"; ?>');
        $this->expectException(DomainException::class); (new MediaStorage())->saveImage($this->uploaded($path, 'photo.jpg'), 'berita');
    }
    public function testUploadDirectoryTraversalIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class); (new MediaStorage())->deleteImage('../', 'photo.jpg');
    }
    private function spreadsheet(array $row): string
    {
        $path = $this->temporary(); $book = new Spreadsheet(); $book->getActiveSheet()->fromArray(['judul','konten','wisata_id','link','gambar','tanggal'], null, 'A1');
        $book->getActiveSheet()->fromArray($row, null, 'A2'); (new Xlsx($book))->save($path); $book->disconnectWorksheets(); return $path;
    }
    public function testImportProducesDraftsAndRejectsUnsafeUrl(): void
    {
        $reader = new NewsImport();
        $items = $reader->read($this->spreadsheet(['Judul berita', 'Konten wisata yang valid.', 901, 'https://example.test/news', '', '2026-10-04']), [901]);
        $this->assertSame('draft', $items[0]['status']); $this->assertSame(901, $items[0]['wisata_id']);
        $this->expectException(DomainException::class);
        $reader->read($this->spreadsheet(['Judul berita', 'Konten wisata yang valid.', 901, 'javascript:alert(1)', '', '2026-10-04']), [901]);
    }
    public function testImportDoesNotEvaluateFormula(): void
    {
        $this->expectException(DomainException::class);
        (new NewsImport())->read($this->spreadsheet(['=1+1','Konten wisata yang valid.',901,'','','2026-10-04']),[901]);
    }
    public function testUrlAndVideoHelpersRejectExecutableAndUntrustedSchemes(): void
    {
        $this->assertSame('', safe_url('javascript:alert(1)')); $this->assertSame('', safe_url('data:text/html,<script>'));
        $this->assertSame('', youtube_embed('https://evil.example/embed/DwZdUKfUvxw'));
        $this->assertSame('https://www.youtube-nocookie.com/embed/DwZdUKfUvxw', youtube_embed('https://youtu.be/DwZdUKfUvxw'));
    }
}