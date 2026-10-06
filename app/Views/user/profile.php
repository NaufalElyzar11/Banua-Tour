<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-wrap">
    <span class="eyebrow">AKUN ANDA</span><h1>Profil dan preferensi</h1><p class="muted">Atur informasi akun dan jenis wisata yang ingin Anda temukan.</p>
    <?php foreach (['success', 'error'] as $type): if ($message = session()->getFlashdata($type)): ?><div class="notice <?= $type === 'error' ? 'notice-error' : '' ?>" role="<?= $type === 'error' ? 'alert' : 'status' ?>"><?= esc($message) ?></div><?php endif; endforeach; ?>
    <div class="profile-grid">
        <section class="form-panel"><h2>Informasi akun</h2>
            <form action="<?= base_url('profile/update') ?>" method="post" class="stack-form"><?= csrf_field() ?>
                <label for="nama">Nama lengkap</label><input id="nama" name="nama" value="<?= esc($user['nama'], 'attr') ?>" autocomplete="name" minlength="2" maxlength="100" required>
                <label for="username">Nama pengguna</label><input id="username" name="username" value="<?= esc($user['username'], 'attr') ?>" autocomplete="username" minlength="3" maxlength="50" pattern="[a-zA-Z0-9_-]+" required>
                <label for="email">Email</label><input type="email" id="email" name="email" value="<?= esc($user['email'], 'attr') ?>" autocomplete="email" maxlength="254" required>
                <label for="daerah">Daerah pilihan <span class="muted">(opsional)</span></label><input id="daerah" name="daerah" value="<?= esc($user['daerah'] === 'Belum diatur' ? '' : $user['daerah'], 'attr') ?>" maxlength="100" list="region-options" placeholder="Misalnya Banjarmasin">
                <datalist id="region-options"><?php foreach (['Banjarmasin','Banjarbaru','Banjar','Barito Kuala','Tapin','Hulu Sungai Selatan','Hulu Sungai Tengah','Hulu Sungai Utara','Tanah Laut','Tanah Bumbu','Kotabaru','Tabalong','Balangan'] as $region): ?><option value="<?= esc($region, 'attr') ?>"><?php endforeach; ?></datalist>
                <p class="muted">Digunakan untuk menampilkan destinasi di daerah pilihan Anda.</p><button class="primary-button" type="submit">Simpan profil</button>
            </form>
        </section>
        <div>
            <section class="form-panel mb-4"><h2>Minat wisata</h2><p class="muted">Pilih beberapa kategori, atau kosongkan untuk menjelajahi semuanya.</p>
                <form action="<?= base_url('profile/updatePreferences') ?>" method="post"><?= csrf_field() ?><fieldset><legend class="visually-hidden">Kategori favorit</legend><div class="preference-options"><?php foreach ($allCategories as $category): ?><label><input type="checkbox" name="kategori_ids[]" value="<?= (int) $category['kategori_id'] ?>" <?= in_array($category['kategori_id'], $userPreferences) ? 'checked' : '' ?>> <?= esc($category['nama_kategori']) ?></label><?php endforeach; ?></div></fieldset><button type="submit" class="primary-button mt-3">Simpan minat</button></form>
            </section>
            <section class="form-panel"><h2>Ubah kata sandi</h2>
                <form action="<?= base_url('profile/change-password') ?>" method="post" class="stack-form"><?= csrf_field() ?>
                    <label for="current_password">Kata sandi saat ini</label><input type="password" id="current_password" name="current_password" autocomplete="current-password" maxlength="1024" required>
                    <label for="new_password">Kata sandi baru</label><input type="password" id="new_password" name="new_password" autocomplete="new-password" minlength="12" maxlength="72" aria-describedby="password-help" required><p id="password-help" class="muted">Gunakan 12–72 karakter. Kalimat yang mudah diingat bisa menjadi pilihan.</p>
                    <label for="confirm_password">Ulangi kata sandi baru</label><input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" minlength="12" maxlength="72" required><button type="submit" class="primary-button">Ubah kata sandi</button>
                </form>
            </section>
        </div>
    </div>
</div>
<?= $this->endSection() ?>