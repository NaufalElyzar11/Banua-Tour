<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\KategoriModel;

class Profile extends BaseController
{
    public function index()
    {
        $user = (new UserModel())->find(session('user_id'));
        return view('user/profile', [
            'title' => 'Profil dan preferensi', 'user' => $user,
            'userPreferences' => array_column(db_connect()->table('minat_user')->where('user_id', session('user_id'))->get()->getResultArray(), 'kategori_id'),
            'allCategories' => (new KategoriModel())->findAll(),
        ]);
    }

    public function update()
    {
        $id = (int) session('user_id');
        if (!$this->validate([
            'nama' => 'required|min_length[2]|max_length[100]',
            'username' => "required|min_length[3]|max_length[50]|alpha_dash|is_unique[users.username,user_id,{$id}]",
            'email' => "required|valid_email|max_length[254]|is_unique[users.email,user_id,{$id}]",
            'daerah' => 'permit_empty|max_length[100]',
        ])) return redirect()->to(base_url('profile'))->with('error', 'Periksa nama, email, dan nama pengguna. Email/nama pengguna harus belum dipakai akun lain.');
        $data = [
            'nama' => trim($this->request->getPost('nama')), 'username' => $this->request->getPost('username'),
            'email' => strtolower(trim($this->request->getPost('email'))),
            'daerah' => trim($this->request->getPost('daerah') ?? '') ?: 'Belum diatur',
        ];
        try {
            if (!(new UserModel())->update($id, $data)) throw new \RuntimeException('Profil belum tersimpan.');
            session()->set($data);
            return redirect()->to(base_url('profile'))->with('success', 'Profil berhasil diperbarui.');
        } catch (\Throwable $e) {
            log_message('error', 'Profile update failed for user {id}', ['id' => $id]);
            return redirect()->to(base_url('profile'))->with('error', 'Profil belum tersimpan. Email atau nama pengguna mungkin sudah dipakai.');
        }
    }

    public function updatePreferences()
    {
        $selected = $this->request->getPost('kategori_ids') ?? [];
        $valid = array_map('strval', array_column((new KategoriModel())->findAll(), 'kategori_id'));
        if (!is_array($selected) || count($selected) > count($valid)) return redirect()->to(base_url('profile'))->with('error', 'Pilihan kategori tidak valid.');
        foreach ($selected as $id) {
            if (!is_scalar($id) || !in_array((string) $id, $valid, true)) return redirect()->to(base_url('profile'))->with('error', 'Kategori tidak ditemukan.');
        }
        $db = db_connect();
        $db->transBegin();
        try {
            $db->table('minat_user')->where('user_id', session('user_id'))->delete();
            foreach (array_unique($selected) as $id) $db->table('minat_user')->insert(['user_id' => session('user_id'), 'kategori_id' => $id]);
            if (!$db->transStatus()) throw new \RuntimeException('Preferensi belum tersimpan.');
            $db->transCommit();
            return redirect()->to(base_url('profile'))->with('success', 'Preferensi wisata berhasil diperbarui.');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to(base_url('profile'))->with('error', 'Preferensi belum tersimpan. Silakan coba lagi.');
        }
    }

    public function changePassword()
    {
        if (!$this->validate([
            'current_password' => 'required|max_length[1024]',
            'new_password' => 'required|min_length[12]|max_length[72]',
            'confirm_password' => 'required|matches[new_password]',
        ])) return redirect()->to(base_url('profile'))->with('error', 'Kata sandi baru harus 12–72 karakter dan konfirmasinya harus sama.');
        $model = new UserModel();
        $user = $model->find(session('user_id'));
        if (!$user || !password_verify($this->request->getPost('current_password'), $user['password'])) return redirect()->to(base_url('profile'))->with('error', 'Kata sandi saat ini tidak cocok.');
        if (!$model->update($user['user_id'], ['password' => $this->request->getPost('new_password')])) return redirect()->to(base_url('profile'))->with('error', 'Kata sandi belum berhasil diubah.');
        session()->regenerate(true);
        return redirect()->to(base_url('profile'))->with('success', 'Kata sandi berhasil diubah.');
    }
}