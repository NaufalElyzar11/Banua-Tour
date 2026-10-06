<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BeritaModel;
use App\Models\WisataModel;
use App\Libraries\MediaStorage;
use App\Libraries\NewsImport;
use DomainException;

class Berita extends BaseController
{
    public function index() { return view('admin/berita/index', ['title' => 'Manajemen Berita', 'berita' => (new BeritaModel())->orderBy('berita_id', 'DESC')->findAll()]); }
    public function create() { return view('admin/berita/create', ['title' => 'Tambah Berita', 'berita' => null, 'wisataList' => (new WisataModel())->findAll()]); }
    public function edit($id)
    {
        $news = (new BeritaModel())->find($id);
        if (!$news) return redirect()->to(base_url('admin/berita'))->with('error', 'Berita tidak ditemukan.');
        return view('admin/berita/edit', ['title' => 'Edit Berita', 'berita' => $news, 'wisataList' => (new WisataModel())->findAll()]);
    }
    public function store() { return $this->save(); }
    public function update($id) { return $this->save((int) $id); }

    private function save(?int $id = null)
    {
        $model = new BeritaModel(); $old = $id ? $model->find($id) : null;
        if ($id && !$old) return redirect()->to(base_url('admin/berita'))->with('error', 'Berita tidak ditemukan.');
        if (!$this->validate([
            'judul' => 'required|min_length[5]|max_length[255]', 'konten' => 'required|min_length[10]|max_length[50000]',
            'status' => 'required|in_list[published,draft]', 'wisata_id' => 'permit_empty|is_natural_no_zero',
            'link_berita' => 'permit_empty|max_length[255]', 'gambar_url' => 'permit_empty|max_length[255]',
        ])) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        $storage = new MediaStorage(); $newImage = null;
        try {
            $destination = $this->request->getPost('wisata_id');
            if ($destination && !(new WisataModel())->find($destination)) throw new DomainException('Wisata tidak ditemukan.');
            $link = trim($this->request->getPost('link_berita') ?? ''); $imageUrl = trim($this->request->getPost('gambar_url') ?? '');
            if (($link !== '' && !safe_url($link)) || ($imageUrl !== '' && !safe_url($imageUrl))) throw new DomainException('URL harus berupa alamat HTTP atau HTTPS yang valid.');
            $data = ['judul' => trim($this->request->getPost('judul')), 'konten' => $this->request->getPost('konten'), 'status' => $this->request->getPost('status'), 'wisata_id' => $destination ?: null, 'link_berita' => $link ?: null, 'tanggal_post' => $old['tanggal_post'] ?? date('Y-m-d')];
            $file = $this->request->getFile('gambar');
            if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) $data['gambar'] = $newImage = $storage->saveImage($file, 'berita');
            elseif ($imageUrl !== '') $data['gambar'] = $imageUrl;
            if (!($id ? $model->update($id, $data) : $model->insert($data))) throw new \RuntimeException('Berita belum tersimpan.');
            if (isset($data['gambar']) && !empty($old['gambar']) && $old['gambar'] !== $data['gambar']) $storage->deleteImage('berita', $old['gambar']);
            return redirect()->to(base_url('admin/berita'))->with('success', 'Berita berhasil disimpan.');
        } catch (\Throwable $e) {
            if ($newImage) $storage->deleteImage('berita', $newImage);
            return redirect()->back()->withInput()->with('error', $e instanceof DomainException ? $e->getMessage() : 'Berita belum tersimpan. Silakan coba lagi.');
        }
    }

    public function delete($id)
    {
        $model = new BeritaModel(); $news = $model->find($id);
        if (!$news || !$model->delete($id)) return redirect()->to(base_url('admin/berita'))->with('error', 'Berita belum berhasil dihapus.');
        if (!empty($news['gambar'])) (new MediaStorage())->deleteImage('berita', $news['gambar']);
        return redirect()->to(base_url('admin/berita'))->with('success', 'Berita berhasil dihapus.');
    }

    public function import()
    {
        if (!$this->validate(['excel_file' => 'uploaded[excel_file]|max_size[excel_file,5120]|ext_in[excel_file,xlsx]'])) return redirect()->to(base_url('admin/berita'))->with('error', 'Pilih file XLSX maksimal 5 MB.');
        $db = db_connect(); $begun = false;
        try {
            $data = (new NewsImport())->read($this->request->getFile('excel_file')->getTempName(), array_column((new WisataModel())->findAll(), 'wisata_id'));
            $db->transBegin(); $begun = true;
            $model = new BeritaModel($db);
            foreach ($data as $item) if (!$model->insert($item)) throw new \RuntimeException('Impor gagal.');
            if (!$db->transStatus()) throw new \RuntimeException('Impor gagal.');
            $db->transCommit();
            return redirect()->to(base_url('admin/berita'))->with('success', count($data) . ' berita diimpor sebagai draf. Periksa sebelum menerbitkan.');
        } catch (\Throwable $e) {
            if ($begun) $db->transRollback();
            return redirect()->to(base_url('admin/berita'))->with('error', $e instanceof DomainException ? $e->getMessage() : 'File belum berhasil diimpor. Periksa format XLSX.');
        }
    }
}
