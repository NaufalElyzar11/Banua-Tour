<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url(session('role') === 'admin' ? 'admin' : 'user_home'));
        }
        return view('auth/login', ['title' => 'Masuk']);
    }

    public function doLogin()
    {
        if (!$this->validate(['email' => 'required|max_length[254]', 'password' => 'required|max_length[1024]'])) {
            return redirect()->to(base_url('auth/login'))->with('error', 'Isi email atau nama pengguna dan kata sandi.');
        }
        $identifier = trim($this->request->getPost('email'));
        $password = $this->request->getPost('password');
        $model = new UserModel();
        $user = $model->groupStart()->where('email', $identifier)->orWhere('username', $identifier)->groupEnd()->first();
        // Verify on both paths to reduce account enumeration by timing.
        $hash = $user['password'] ?? '$argon2id$v=19$m=65536,t=4,p=1$VFZZY1VGR1ovSkJlSHhOVQ$uvzXj8rL99tLHOnTxWiPp3WU2HpFesvoYrA9P1asxhU';
        if (!password_verify($password, $hash) || !$user) {
            session()->setFlashdata('_ci_old_input', ['get' => [], 'post' => ['email' => $identifier]]);
            return redirect()->to(base_url('auth/login'))->with('error', 'Email/nama pengguna atau kata sandi salah.');
        }
        if (password_needs_rehash($user['password'], PASSWORD_ARGON2ID)) {
            $model->update($user['user_id'], ['password' => $password]);
        }
        $returnTo = session()->get('return_to');
        session()->remove('return_to');
        session()->regenerate(true);
        session()->set([
            'user_id' => $user['user_id'], 'nama' => $user['nama'], 'email' => $user['email'],
            'username' => $user['username'], 'role' => $user['role'], 'daerah' => $user['daerah'],
            'isLoggedIn' => true,
        ]);
        $target = $user['role'] === 'admin' ? 'admin' : 'user_home';
        if ($user['role'] !== 'admin' && is_string($returnTo) && preg_match('~\A(?:booking/pembelian/[0-9]+|profile|wishlist|riwayat)\z~', $returnTo)) {
            $target = $returnTo;
        }
        return redirect()->to(base_url($target));
    }

    public function register()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('user_home'));
        }
        return view('auth/register', ['title' => 'Daftar']);
    }

    public function doRegister()
    {
        $rules = [
            'nama' => 'required|min_length[2]|max_length[100]',
            'username' => 'required|min_length[3]|max_length[50]|alpha_dash|is_unique[users.username]',
            'email' => 'required|valid_email|max_length[254]|is_unique[users.email]',
            'password' => 'required|min_length[12]|max_length[72]',
            'confirm_password' => 'required|matches[password]',
        ];
        if (!$this->validate($rules)) {
            session()->setFlashdata('_ci_old_input', ['get' => [], 'post' => array_intersect_key(
                $this->request->getPost(), array_flip(['nama', 'username', 'email'])
            )]);
            return redirect()->to(base_url('auth/register'))->with('errors', $this->validator->getErrors());
        }
        $model = new UserModel();
        try {
        if (!$model->insert([
            'nama' => trim($this->request->getPost('nama')), 'username' => $this->request->getPost('username'),
            'email' => strtolower(trim($this->request->getPost('email'))), 'password' => $this->request->getPost('password'),
            'daerah' => 'Belum diatur', 'jenis_kelamin' => null, 'umur' => null, 'role' => 'user',
        ])) {
            return redirect()->to(base_url('auth/register'))->with('error', 'Akun belum berhasil dibuat. Silakan coba lagi.');
        }
        } catch (\Throwable $e) {
            return redirect()->to(base_url('auth/register'))->with('error', 'Akun belum berhasil dibuat. Email atau nama pengguna mungkin sudah dipakai.');
        }
        return redirect()->to(base_url('auth/login'))->with('success', 'Akun berhasil dibuat. Silakan masuk.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('/'));
    }
}
