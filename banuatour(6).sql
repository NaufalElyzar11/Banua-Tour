-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 31, 2026 at 11:50 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `banuatour`
--

-- --------------------------------------------------------

--
-- Table structure for table `berita`
--

CREATE TABLE `berita` (
  `berita_id` int UNSIGNED NOT NULL,
  `judul` varchar(150) NOT NULL,
  `konten` text NOT NULL,
  `wisata_id` int UNSIGNED DEFAULT NULL,
  `link_berita` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `gambar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `tanggal_post` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `berita`
--

INSERT INTO `berita` (`berita_id`, `judul`, `konten`, `wisata_id`, `link_berita`, `gambar`, `tanggal_post`) VALUES
(1, 'Disebut Mirip Raja Ampat, Ini Bukti Keindahan Bukit Matang Kaladan Kalsel', 'Memiliki pesona yang tak kalah cantik dengan destinasi terkenal seperti Raja Ampat, Papua Barat. Membuat Bukit Matang Kaladan di Kabupaten Banjar, Kalimantan Selatan, layak dijadikan destinasi wisata alam.', 1, 'https://www.liputan6.com/regional/read/5317464/disebut-mirip-raja-ampat-ini-bukti-keindahan-bukit-matang-kaladan-kalsel', 'https://i.imgur.com/rT9sLKg.jpeg', '2023-06-04'),
(2, 'Pulau Kembang, Pulaunya Para Kera', 'Pulau Kembang yang termasuk di dalam wilayah Kecamatan Alalak, Kabupaten Barito Kuala ternyata merupakan habitat bagi kera berekor panjang (monyet). Konon katanya pun apabila pengunjung sedang beruntung juga dapat melihat kemunculan kera berwarna putih.', 2, 'https://indonesiakaya.com/pustaka-indonesia/pulau-kembang-pulaunya-para-kera/', 'https://i.imgur.com/mNgqSRL.jpeg', '2021-02-27'),
(3, 'Intip Pesona Bukit Rimpi Pelaihari, Sabana Indah dari Kalimantan', 'Bukit Rimpi Pelaihari,Bukit hijau nan indah ini lokasinya ada di Kota Pelaihari, Kalimantan Selantan. Karena pemandangannya yang asri bak negeri dongeng, tak jarang warga dan wisatawan lebih sering menyebutnya sebagai Bukit Teletubbies.', 3, 'https://indonesiakaya.com/pustaka-indonesia/pulau-kembang-pulaunya-para-kera/', 'https://i.imgur.com/lEmWoqo.jpeg', '2017-02-02'),
(4, 'Mandiangin Tahura Sultan Adam Surga Ekowisata di Tengah Hutan Kalsel Bagian 2', 'Mandiangin Tahura Sultan Adam di Kalimantan Selatan merupakan destinasi ekowisata yang menawarkan keindahan alam dan berbagai aktivitas rekreasi. Dengan luas sekitar 112.000 hektare, kawasan ini mencakup hutan lindung, suaka margasatwa, dan area pendidikan.', 4, 'https://kalsel.antaranews.com/video/4506129/feature-mandiangin-tahura-sultan-adam-surga-ekowisata-di-tengah-hutan-kalsel-bagian-2', 'https://i.imgur.com/LXwy2cY.jpeg', '2024-12-02'),
(5, 'Amanah Borneo Park: Destinasi Wisata Keluarga Terbesar di Kalimantan.', 'Amanah Borneo Park merupakan taman wisata rekreasi dan edukasi yang berada di Kalimantan Selatan. Tempat ini menghadirkan sejumlah fasiitas dan wahana. Selain bisa bersenang-senang, pengunjung juga bisa mempelajari lebih jauh mengenai lingkungan dan alam sekitar.', 5, 'https://kumparan.com/jendela-dunia/amanah-borneo-park-destinasi-wisata-keluarga-terbesar-di-kalimantan-24Yv4PNZJdE/1', 'https://i.imgur.com/2xWavQl.jpeg', '2025-02-24'),
(6, 'Feature - Kebun Raya Banua Wisata Alam di Tengah Kota Banjarbaru (Bagian 1)', 'Kebun Raya Banua di Kalimantan Selatan menjadi destinasi favorit bagi pecinta alam. Dengan luas lebih dari 100 hektare, kebun raya ini menawarkan pemandangan yang memukau dan keanekaragaman flora yang luar biasa.', 6, 'https://kalsel.antaranews.com/video/4493897/feature-kebun-raya-banua-wisata-alam-di-tengah-kota-banjarbaru-bagian-1', 'https://i.imgur.com/k9P9Oij.jpeg', '2024-11-26'),
(7, 'Air Terjun Haratai, Permata Tersembunyi di Kalimantan Selatan', 'Kalimantan Selatan menyimpan sejuta pesona alam yang memukau, salah satunya adalah Air Terjun Haratai. Terletak di Desa Haratai, Kecamatan Loksado, Kabupaten Hulu Sungai Selatan. Air terjun bertingkat tiga ini menjadi destinasi favorit.', 7, 'https://www.rri.co.id/banjarmasin/berita-foto/17340/air-terjun-haratai-permata-tersembunyi-di-kalimantan-selatan', 'https://i.imgur.com/soB4fOG.jpeg', '2025-01-20'),
(8, 'Bamboo Rafting Balanting Paring Loksado', 'Balanting Paring atau Bamboo Rafting atau Berselancar di Sungai yang berair deras dengan Rakit Bambu, atau Arung Jeram dengan Bambu, bedanya kalau arung jeram menggunakan perahu karet, dan jenis arusnya pun bermacam macam, ada kelas kelas / level nya. Kali ini bawa anak anak outing ke daerah pegunungan Meratus, tepatnya daerah Loksado, Kalimantan Selatan.', 8, 'https://hulusungaiselatankab.go.id/pemkab/bamboo-rafting-balanting-paring-loksado/', 'https://i.imgur.com/PsE9nnh.jpeg', '2017-11-01'),
(9, 'Wisata Kalsel - Ramai dan Hits, Pengunjung Patut Coba River Tubing Menyusuri Sungai Amandit Loksado', 'Objek wisata tubing Loksado, terbilang ramai dan hits di kalangan pengunjung objek wisata alam Loksado. River Tubing, merupakan aktivitas seru, dimana pengunjung menyusuri sungai Amandit menggunakan ban dalam.', 9, 'https://banjarmasin.tribunnews.com/2025/01/28/wisata-kalsel-ramai-dan-hits-pengunjung-patut-coba-river-tubing-menyusuri-sungai-amandit-loksado', 'https://i.imgur.com/IPBMXGT.jpeg', '2025-01-28'),
(10, 'Aya Senang Liburan ke Pantai Teluk Tamiang Kotabaru, Selain Penginapan Mudah Tersedia Wahana Bermain', 'Pantai Teluk Tamiang, salah satu destinasi wisata terus diminati pengunjung atau wisatawan dari dan luar Kabupaten Kotabaru, Provinsi Kalimantan Selatan. Obyek wisata berada di Desa Teluk Tamiang, Kecamatan Pulau Laut Tanjung Selayar, Kotabaru atau kabupaten berada di ujung Tenggara Kalimantan Selatan masih menjadi primadona.', 10, 'https://kalimantanlive.com/2025/03/07/aya-senang-liburan-ke-pantai-teluk-tamiang-kotabaru-selain-penginapan-mudah-tersedia-wahana-bermain/', 'https://i.imgur.com/iNIavC6.jpeg', '2025-03-07');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `wisata_id` int UNSIGNED NOT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `jumlah_orang` int NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status` enum('upcoming','completed','canceled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'upcoming',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `kode_tiket` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `wisata_id`, `tanggal_kunjungan`, `jumlah_orang`, `total_harga`, `status`, `created_at`, `kode_tiket`) VALUES
(2, 2, 1, '2025-06-16', 1, '10000.00', 'completed', '2025-06-14 09:30:38', 'TIKET-C81E72-1750231240'),
(4, 2, 2, '2025-06-16', 1, '40000.00', 'completed', '2025-06-15 04:53:25', NULL),
(5, 4, 1, '2025-06-18', 3, '30000.00', 'completed', '2025-06-16 10:13:13', NULL),
(21, 5, 3, '2025-06-25', 5, '75000.00', 'completed', '2025-06-19 19:06:48', 'TIKET-3C59DC-1750388812');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `kategori_id` int UNSIGNED NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`kategori_id`, `nama_kategori`) VALUES
(2, 'Alam'),
(6, 'Budaya'),
(1, 'Bukit'),
(3, 'Hiburan'),
(5, 'Kota'),
(4, 'Pantai');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint UNSIGNED NOT NULL,
  `version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2024-01-01-000001', 'App\\Database\\Migrations\\CreateUsersTable', 'default', 'App', 1748941438, 1),
(2, '2024-01-01-000002', 'App\\Database\\Migrations\\CreateMinatTable', 'default', 'App', 1748941438, 1),
(3, '2024-01-01-000003', 'App\\Database\\Migrations\\CreateUserMinatTable', 'default', 'App', 1748941438, 1),
(4, '2024-01-01-000004', 'App\\Database\\Migrations\\CreateWisataTable', 'default', 'App', 1748941438, 1),
(5, '2024-01-01-000005', 'App\\Database\\Migrations\\CreateReviewTable', 'default', 'App', 1748941438, 1),
(6, '2024-01-01-000006', 'App\\Database\\Migrations\\CreateBeritaTable', 'default', 'App', 1748941438, 1),
(7, '2024-01-01-000007', 'App\\Database\\Migrations\\CreatePemesananTable', 'default', 'App', 1748941438, 1),
(8, '2024-01-01-000008', 'App\\Database\\Migrations\\CreatePembayaranTable', 'default', 'App', 1748941438, 1),
(9, '2024-01-01-000009', 'App\\Database\\Migrations\\CreateWishlistTable', 'default', 'App', 1748941438, 1),
(10, '2024-01-01-000010', 'App\\Database\\Migrations\\CreateStatistikKunjunganTable', 'default', 'App', 1748941438, 1);

-- --------------------------------------------------------

--
-- Table structure for table `minat_user`
--

CREATE TABLE `minat_user` (
  `user_id` int UNSIGNED NOT NULL,
  `kategori_id` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `minat_user`
--

INSERT INTO `minat_user` (`user_id`, `kategori_id`) VALUES
(5, 1),
(3, 2),
(5, 2),
(5, 3),
(2, 4),
(5, 4),
(5, 5),
(5, 6);

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `review_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `wisata_id` int UNSIGNED NOT NULL,
  `rating` int NOT NULL,
  `komentar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_review` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`review_id`, `user_id`, `wisata_id`, `rating`, `komentar`, `tanggal_review`) VALUES
(2, 4, 1, 5, 'Pemandangan indah\r\n', '2025-06-16 20:02:29'),
(13, 5, 2, 5, 'monyetnya lucu', '2025-06-18 18:48:05'),
(15, 5, 15, 5, 'seru bangettt, cuman pengelolaan tempatnya aja lagi yang kurang bersih dan rapi', '2025-06-19 19:01:51'),
(16, 5, 3, 5, 'pemandangannya luar biasaa', '2025-06-19 19:07:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `daerah` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `umur` int NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `nama`, `username`, `email`, `password`, `daerah`, `jenis_kelamin`, `umur`, `role`, `created_at`) VALUES
(2, 'Naufal Elyzar', 'Nopal', 'naufal@gmail.com', '$2y$10$fQbARjCJs1N06TC0jDFgne6p8ZN8eXHZt0xymtSlMJCNWwKlP4ThO', 'Hulu Sungai Selatan', '', 0, 'user', '2025-06-03 10:03:45'),
(3, 'Aufa', 'Fitrianda', 'aufa@gmail.com', '$2y$10$lrid7joTswLG74AhchASxeMA5LfIE1rscVgg058SPHiY34nHr3MMO', 'Hulu Sungai Selatan', '', 0, 'user', '2025-06-09 04:20:40'),
(4, 'admin', 'admin', 'banuatour@gmail.com', '$2y$10$fQbARjCJs1N06TC0jDFgne6p8ZN8eXHZt0xymtSlMJCNWwKlP4ThO', 'Hulu Sungai Selatan', 'L', 22, 'admin', '2025-06-13 10:03:45'),
(5, 'Muhammad Rizki Ramadhan', 'iki_madan', 'mr.rizkirmdhn@gmail.com', '$2y$12$cAR7yoF/Te6O0j53odKNK.3g3GwEf1vvoMZgMZGuUj9fBrkThuBiu', 'Tanah Laut', '', 0, 'user', '2025-06-19 02:43:37'),
(6, 'Harry Pratama', 'Heri', 'harry@gmail.com', '$2y$10$4UGa.vWgTAfwA/61Z7X/suwicFBVA/N7rS8IEd/yEZWtGSiHjg57y', 'Banjarmasin', 'L', 20, 'user', '2025-06-22 03:10:17'),
(7, 'Raymond', 'Emon', 'emon@gmail.com', '$2y$10$72y1A7GYPh/Xpr8ufT0KG.2xO02wieIsAoGt/XjRt2E9yS2toCtfS', 'Tanah Laut', 'L', 20, 'user', '2025-06-22 03:11:21'),
(8, 'Yosnandia Dwi Nurahma', 'Nadia', 'nadia@gmail.com', '$2y$10$3Qk5Qj1iuZLYUUkHe2YacOODxMh3ZQ8Hd1XGdA4wrF0TyqvEvyXZa', 'Banjarbaru', 'P', 21, 'user', '2026-05-30 18:22:08');

-- --------------------------------------------------------

--
-- Table structure for table `wisata`
--

CREATE TABLE `wisata` (
  `wisata_id` int UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `daerah` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `deskripsi` text NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `kategori_id` int UNSIGNED DEFAULT NULL,
  `gambar_wisata` varchar(255) DEFAULT NULL,
  `link_video` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `wisata`
--

INSERT INTO `wisata` (`wisata_id`, `nama`, `daerah`, `deskripsi`, `harga`, `kategori_id`, `gambar_wisata`, `link_video`, `created_at`, `latitude`, `longitude`) VALUES
(1, 'Bukit Matang Kaladan', 'Banjar', 'Bukit Matang Kaladan terletak di Desa Tiwingan Lama, Kecamatan Aranio, Kabupaten Banjar, Kalimantan Selatan. Bukit Matang Kaladan banyak dikunjungi wisatawan lokal dan mancanegara. Hal tersebut karena keindahan alam di kawasan objek wisata ini. Bahkan keindahan alamnya sudah dapat dinikmati selama perjalanan menuju puncak bukit.', '10000.00', 1, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSjnzE0KL_o5ktztpiaDVgZwnWEpC6JwMtubqqYe0X6a0F3iePLyfmS2vtoeQ&s', 'https://www.youtube.com/embed/DwZdUKfUvxw?si=RUJZL-CowH5rTi20', '2025-06-09 05:04:15', '-3.52523163', '115.00944863'),
(2, 'Pulau Kembang', 'Barito Kuala', 'Tinggiran II, Kec. Tamban, Kabupaten Barito Kuala, Kalimantan Selatan\r\n\r\nPulau Kembang merupakan habitat bagi kera ekor panjang (monyet) dan beberapa jenis burung. Kawasan pulau Kembang juga merupakan salah satu objek wisata yang berada di dalam kawasan hutan di Kabupaten Barito Kuala.', '40000.00', 2, 'https://i.imgur.com/Memvudf.jpeg', 'https://www.youtube.com/embed/aGkLoxPW93o?si=Zrk6oW0NnTew6iEO', '2025-06-09 05:04:15', '-3.29976000', '114.56072200'),
(3, 'Bukit Rimpi', 'Tanah Laut', 'Bukit Rimpi merupakan sebuah perbukitan yang ditumbuhi oleh padang savana hijau yang dihiasi oleh hamparan rumput dengan pemandangannya yang sangat elok. Bukit cantik ini lebih dikenal dengan sebutan Bukit Teletubies', '15000.00', 1, 'https://i.imgur.com/kCzt4iM.jpeg', NULL, '2025-06-09 05:04:15', '-3.84633943', '114.79197368'),
(4, 'Taman Hutan Raya Sultan Adam', 'Banjar', 'Kawasan ini merupakan destinasi unggulan di Kalimantan Selatan yang di kelola oleh Unit Pelaksana Teknis (UPT) dari Dinas Kehutanan Provinsi Kalimantan Selatan. Tahura Sultan Adam ini merupakan bagian dari Geopark Meratus yang tengah dikembangkan menjadi Geopark Internasional.', '25000.00', 2, 'https://i.imgur.com/NHlbchB.jpeg', NULL, '2025-06-09 05:04:15', '-3.50308542', '114.93479683'),
(5, 'Amanah Borneo Park', 'Banjarbaru', 'Amanah Borneo Park adalah destinasi wisata keluarga terbesar di Kalimantan. Destinasi ini terletak di Banjarbaru Kalimantan Selatan, taman hiburan ini menawarkan beragam wahana menarik, mulai dari taman bermain anak, wahana adrenalin, hingga area petik buah.', '75000.00', 3, 'https://i.imgur.com/NCGWSjM.jpeg', NULL, '2025-06-09 05:04:15', '-3.49551864', '114.80877473'),
(6, 'Kebun Raya Banua', 'Banjarbaru', 'Kebun raya Banua adalah kebun raya dengan spesifikasi tanaman obat dan konservasi tanaman langka khas Kalimantan yang berlokasi di kawasan pusat perkantoran Pemerintah Provinsi kalimantan Selatan, tepatnya di Jalan Aneka Tambang, Kelurahan Palam, Kecamatan Cempaka, Banjarbaru, Kalimantan Selatan dengan luas 100 hektar.', '7000.00', 2, 'https://i.imgur.com/vgsY3zA.jpeg', NULL, '2025-06-09 05:04:15', '-3.48959163', '114.81663164'),
(7, 'Air Terjun Haratai Loksado', 'Hulu Sungai Selatan', 'Air Terjun Haratai menjadi salah satu air terjun di Kalimantan Selatan yang bentuknya unik. Aliran air terjun di sini memiliki tiga tingkat, berbeda dengan air terjun pada umumnya yang hanya memiliki satu.', '10000.00', 2, 'https://i.imgur.com/kbObbpj.jpeg', NULL, '2025-06-09 05:04:15', '-2.77492623', '115.54589892'),
(8, 'Balanting', 'Hulu Sungai Selatan', 'Balanting Paring atau Bamboo Rafting atau Berselancar di Sungai yang berair deras dengan Rakit Bambu, atau Arung Jeram dengan Bambu, bedanya kalau arung jeram menggunakan perahu karet, dan jenis arusnya pun bermacam macam, ada kelas kelas / level nya. Kali ini bawa anak anak outing ke daerah pegunungan Meratus, tepatnya daerah Loksado, Kalimantan Selatan.', '300000.00', 2, 'https://i.imgur.com/FbaNmzU.jpeg', NULL, '2025-06-09 05:04:15', '-2.79532004', '115.49460548'),
(9, 'Tubing', 'Hulu Sungai Selatan', 'river tubing, yakni naik ban renang ukuran besar menyusuri Sungai Amandit.', '50000.00', 2, 'https://i.imgur.com/3501Myg.jpeg', NULL, '2025-06-09 05:04:15', '-2.80310294', '115.49886500'),
(10, 'Teluk Tamiang', 'Kotabaru', 'Pantai Teluk Tamiang merupakan salah satu destinasi wisata andalan Kabupaten Kotabaru, Kalimantan Selatan. Airnya yang biru jernih dilengkapi hamparan pasir putih menjadi daya tariknya sendiri.', '10000.00', 4, 'https://i.imgur.com/TYxtkkT.jpeg', NULL, '2025-06-09 05:04:15', '-4.04565383', '116.05073757'),
(15, 'Pantai Takisung', 'Tanah Laut', 'Wisata Pantai Takisung merupakan salah satu wisata andalan bagi Kabupaten Tanah Laut. Pantai Takisung memiliki sarana dan prasarana seperti wc umum, kamar mandi, area parkir, pasar ikan dan buah-buahan, warung yang menjual cendera mata, makanan dan minuman, panggung, halte, pos polisi, shalter (tempat berteduh), restoran dan tempat bermain.', '10000.00', 4, NULL, 'https://youtu.be/mULDHPEadyY?si=wtiEarRkGFpOGqvO', '2025-06-17 16:47:27', '-3.86444425', '114.61059932'),
(19, 'Taman Satwa Jahri Saleh', 'Banjarmasin', 'Kebun Binatang Banjarmasin yang juga dikenal dengan nama Kebun Binatang Mini Jahri Saleh merupakan kebun binatang satu-satunya yang berada di kota yang berjuluk Kota Seribu Sungai ini. \r\nAda beberapa fasilitas yang disediakan oleh kebun binatang ini salah satunya ialah fasilitas bermain anak. Fasilitas bermain tersebut terletak di tengah-tengah Taman Satwa Jahari Saleh. Disini tersedia berbagai permainan yang disukai oleh anak-anak diantaranya jungkat-jungkit, ayunan, permainan kursi putar dan masih banyak lagi.\r\nYang paling iconic adalah Zona Primata. Salah satu jenis koleksi primatanya adalah orang utan.\r\nKebun binatangnya tak terlalu besar, sehingga Anda bisa mengajak anak-anak berkeliling cukup dengan berjalan kaki.\r\nBila lelah jalan kaki, Anda bisa beristirahat di bangunan pendopo atau pondokan yang telah disediakan.\r\n', '5000.00', 5, NULL, NULL, '2025-06-20 07:25:26', '0.00000000', '0.00000000');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `wisata_id` int UNSIGNED NOT NULL,
  `tanggal_ditambahkan` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `gambar_wisata` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `user_id`, `wisata_id`, `tanggal_ditambahkan`, `gambar_wisata`) VALUES
(1, 3, 1, '2025-06-09 13:01:37', NULL),
(11, 5, 1, '2025-06-20 03:02:22', NULL),
(12, 5, 3, '2025-06-20 03:06:38', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `berita`
--
ALTER TABLE `berita`
  ADD PRIMARY KEY (`berita_id`),
  ADD KEY `berita_wisata_id_foreign` (`wisata_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `wisata_id` (`wisata_id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`kategori_id`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `minat_user`
--
ALTER TABLE `minat_user`
  ADD PRIMARY KEY (`user_id`,`kategori_id`),
  ADD KEY `fk_minat_kategori_id` (`kategori_id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `review_user_id_foreign` (`user_id`),
  ADD KEY `review_wisata_id_foreign` (`wisata_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wisata`
--
ALTER TABLE `wisata`
  ADD PRIMARY KEY (`wisata_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD KEY `wishlist_wisata_id_foreign` (`wisata_id`),
  ADD KEY `wishlist_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `berita`
--
ALTER TABLE `berita`
  MODIFY `berita_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `kategori_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `review_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `wisata`
--
ALTER TABLE `wisata`
  MODIFY `wisata_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `wishlist_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `berita`
--
ALTER TABLE `berita`
  ADD CONSTRAINT `berita_wisata_id_foreign` FOREIGN KEY (`wisata_id`) REFERENCES `wisata` (`wisata_id`) ON DELETE CASCADE ON UPDATE SET NULL;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`wisata_id`) REFERENCES `wisata` (`wisata_id`) ON DELETE CASCADE;

--
-- Constraints for table `minat_user`
--
ALTER TABLE `minat_user`
  ADD CONSTRAINT `fk_minat_kategori_id` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`kategori_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_minat_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `review_wisata_id_foreign` FOREIGN KEY (`wisata_id`) REFERENCES `wisata` (`wisata_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wishlist_wisata_id_foreign` FOREIGN KEY (`wisata_id`) REFERENCES `wisata` (`wisata_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
