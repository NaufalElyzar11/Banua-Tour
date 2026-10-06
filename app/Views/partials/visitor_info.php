<section class="visitor-info" aria-label="Informasi sebelum berkunjung"><h2>Persiapan kunjungan</h2><dl>
<?php foreach (['jam_buka' => 'Jam buka', 'fasilitas' => 'Fasilitas', 'akses_transportasi' => 'Akses & transportasi', 'aksesibilitas' => 'Kesesuaian untuk anak, lansia & aksesibilitas', 'ketentuan_tiket' => 'Ketentuan tiket & pembatalan', 'kontak_pengelola' => 'Kontak pengelola'] as $key => $label): ?>
<div><dt><?= $label ?></dt><dd><?= esc(!empty($wisata[$key]) ? $wisata[$key] : 'Belum diinformasikan pengelola. Konfirmasikan sebelum berkunjung.') ?></dd></div>
<?php endforeach; ?></dl></section>
