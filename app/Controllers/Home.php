<?php

namespace App\Controllers;

use App\Models\WisataModel;
use App\Models\BeritaModel;
use App\Models\KategoriModel;

class Home extends BaseController
{
    public function index()
    {
        return view('home/index', $this->homeData());
    }

    protected function homeData(): array
    {
        $model = new WisataModel();
        $editorial = $model->whereIn('nama', ['Bukit Matang Kaladan', 'Balanting', 'Teluk Tamiang'])->findAll();
        $byName = array_column($editorial, null, 'nama');
        $journeys = [];
        foreach ([
            ['name' => 'Balanting', 'label' => 'IKUTI ALIRANNYA', 'heading' => 'Sungai, rakit, dan cerita.', 'description' => 'Temukan sisi Banua dari perjalanan di atas air.'],
            ['name' => 'Bukit Matang Kaladan', 'label' => 'AMBIL JALAN KE ATAS', 'heading' => 'Sudut pandang yang baru.', 'description' => 'Sisihkan waktu untuk menikmati bentang alam dari ketinggian.'],
            ['name' => 'Teluk Tamiang', 'photo' => 'main-image.jpg', 'label' => 'TEMUI TEPI LAUT', 'heading' => 'Hari yang lebih pelan.', 'description' => 'Beri ruang untuk angin laut dan suasana pantai.'],
        ] as $journey) {
            if (!isset($byName[$journey['name']])) {
                continue;
            }
            $destination = $byName[$journey['name']];
            $photo = 'uploads/wisata/gallery/' . (int) $destination['wisata_id'] . '/' . ($journey['photo'] ?? 'gallery-1.jpg');
            $journeys[] = $journey + [
                'destination' => $destination,
                'image' => is_file(FCPATH . $photo) ? base_url($photo) : wisata_image($destination),
            ];
        }
        return [
            'title' => 'Wisata Kalimantan Selatan', 'wisataTrending' => $model->getTrendingWisata(4),
            'heroDestination' => $byName['Bukit Matang Kaladan'] ?? null, 'journeys' => $journeys,
            'regions' => array_column((new WisataModel())->select('daerah')->distinct()->orderBy('daerah')->findAll(), 'daerah'),
            'pageClass' => 'home-surface',
            'berita' => (new BeritaModel())->getBeritaTerbaru(3),
            'kategoriList' => (new KategoriModel())->orderBy('nama_kategori')->findAll(),
            'wisataRekomendasi' => [], 'wisataTerdekat' => [], 'userRegion' => '',
        ];
    }
}
