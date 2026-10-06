<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\MediaStorage;
use App\Models\WisataModel;
use App\Models\KategoriModel;
use App\Models\BookingModel;

class Wisata extends BaseController
{
    private array $infoFields = ['jam_buka','fasilitas','akses_transportasi','aksesibilitas','ketentuan_tiket','kontak_pengelola'];

    public function index()
    {
        return view('admin/wisata/index', ['title' => 'Manajemen Destinasi', 'wisata' => (new WisataModel())->getWisataWithKategori()]);
    }

    public function create()
    {
        return view('admin/wisata/create', ['title' => 'Tambah Destinasi', 'wisata' => null, 'kategoriList' => (new KategoriModel())->findAll()]);
    }

    public function edit($id)
    {
        $wisata = (new WisataModel())->find($id);
        if (!$wisata) return redirect()->to(base_url('admin/wisata'))->with('error', 'Destinasi tidak ditemukan.');
        return view('admin/wisata/edit', ['title' => 'Edit Destinasi', 'wisata' => $wisata, 'kategoriList' => (new KategoriModel())->findAll()]);
    }

    public function store()
    {
        return $this->save();
    }

    public function update($id)
    {
        return $this->save((int) $id);
    }

    private function save(?int $id = null)
    {
        $model = new WisataModel();
        if ($id && !$model->find($id)) return redirect()->to(base_url('admin/wisata'))->with('error', 'Destinasi tidak ditemukan.');
        $rules = [
            'nama' => 'required|min_length[3]|max_length[100]', 'daerah' => 'required|max_length[100]',
            'deskripsi' => 'required|min_length[10]|max_length[10000]',
            'harga' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[99999999.99]',
            'kategori_id' => 'required|is_natural_no_zero|is_not_unique[kategori.kategori_id]',
            'latitude' => 'permit_empty|numeric|greater_than_equal_to[-90]|less_than_equal_to[90]',
            'longitude' => 'permit_empty|numeric|greater_than_equal_to[-180]|less_than_equal_to[180]',
            'link_video' => 'permit_empty|valid_url|max_length[500]',
        ];
        foreach ($this->infoFields as $field) $rules[$field] = 'permit_empty|max_length[4000]';
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        if (!preg_match('/\A[0-9]{1,8}(?:\.[0-9]{1,2})?\z/', (string) $this->request->getPost('harga'))) return redirect()->back()->withInput()->with('errors', ['harga' => 'Harga maksimal dua angka desimal.']);
        $video = $this->request->getPost('link_video') ?? '';
        if ($video !== '' && !youtube_embed($video)) return redirect()->back()->withInput()->with('errors', ['link_video' => 'Gunakan tautan video YouTube yang valid.']);
        $data = [
            'nama' => trim($this->request->getPost('nama')), 'daerah' => trim($this->request->getPost('daerah')),
            'deskripsi' => $this->request->getPost('deskripsi'), 'harga' => $this->request->getPost('harga'),
            'kategori_id' => (int) $this->request->getPost('kategori_id'),
            'latitude' => $this->request->getPost('latitude') === '' ? null : $this->request->getPost('latitude'),
            'longitude' => $this->request->getPost('longitude') === '' ? null : $this->request->getPost('longitude'),
            'link_video' => youtube_embed($video),
        ];
        foreach ($this->infoFields as $field) $data[$field] = $this->request->getPost($field) ?: null;
        $files = array_filter($this->request->getFileMultiple('gambar') ?? [], static fn ($file) => $file->getError() !== UPLOAD_ERR_NO_FILE);
        if (count($files) + ($id ? count(wisata_gallery($id)) : 0) > 7) return redirect()->back()->withInput()->with('errors', ['gambar' => 'Galeri maksimal 7 foto. Hapus foto lama jika ingin menggantinya.']);
        $db = db_connect(); $db->transBegin(); $createdFiles = []; $storage = new MediaStorage();
        try {
            if (!$id) {
                $id = (int) $model->insert($data, true);
                if (!$id) throw new \RuntimeException('Destinasi belum tersimpan.');
            } elseif (!$model->update($id, $data)) {
                throw new \RuntimeException('Destinasi belum tersimpan.');
            }
            foreach ($files as $file) $createdFiles[] = $storage->saveImage($file, 'wisata/gallery/' . $id);
            if (!$db->transStatus()) throw new \RuntimeException('Penyimpanan gagal.');
            $db->transCommit();
            return redirect()->to(base_url('admin/wisata'))->with('success', 'Destinasi berhasil disimpan.');
        } catch (\Throwable $e) {
            $db->transRollback();
            foreach ($createdFiles as $file) $storage->deleteImage('wisata/gallery/' . $id, $file);
            log_message('error', 'Penyimpanan destinasi gagal: {exception}', ['exception' => $e]);
            return redirect()->back()->withInput()->with('errors', ['gambar' => $e instanceof \DomainException ? $e->getMessage() : 'Destinasi belum berhasil disimpan. Coba lagi.']);
        }
    }

    public function delete($id)
    {
        if ((new BookingModel())->where('wisata_id', $id)->countAllResults()) {
            return redirect()->to(base_url('admin/wisata'))->with('error', 'Destinasi dengan transaksi tidak dapat dihapus agar riwayat tetap utuh.');
        }
        (new WisataModel())->delete($id);
        return redirect()->to(base_url('admin/wisata'))->with('success', 'Destinasi dihapus.');
    }

    public function deleteImage($id, $filename)
    {
        if (!(new WisataModel())->find($id)) return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Destinasi tidak ditemukan.']);
        $ok = (new MediaStorage())->deleteImage('wisata/gallery/' . (int) $id, $filename);
        return $this->response->setStatusCode($ok ? 200 : 404)->setJSON(['success' => $ok, 'message' => $ok ? 'Foto dihapus.' : 'Foto tidak ditemukan.']);
    }
}
