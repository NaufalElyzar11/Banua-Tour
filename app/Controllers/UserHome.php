<?php

namespace App\Controllers;

use App\Models\WisataModel;
use App\Models\UserModel;

class UserHome extends Home
{
    public function index()
    {
        $data = $this->homeData();
        $user = (new UserModel())->find(session('user_id'));
        $model = new WisataModel();
        $data['wisataRekomendasi'] = $model->getRekomendasiWisataByUserMinat(session('user_id'), 4);
        $data['userRegion'] = $user['daerah'] ?? '';
        $data['wisataTerdekat'] = $model->getWisataTerdekat($data['userRegion'], 4);
        return view('home/index', $data);
    }
}
