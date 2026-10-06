<?php

function safe_url(?string $url): string
{
    if (!$url || preg_match('/[\x00-\x20]/', $url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        return '';
    }
    return in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?? ''), ['https', 'http'], true) ? $url : '';
}

function wisata_gallery(int $id): array
{
    static $galleries = [];
    if (isset($galleries[$id])) {
        return $galleries[$id];
    }
    $folder = FCPATH . 'uploads/wisata/gallery/' . $id;
    $images = [];
    if (is_dir($folder)) {
        foreach (scandir($folder) as $filename) {
            if (preg_match('/\A[a-zA-Z0-9_-][a-zA-Z0-9_.-]*\.(?:jpg|jpeg|png|webp)\z/i', $filename) && is_file($folder . '/' . $filename)) {
                $images[] = 'gallery/' . $id . '/' . $filename;
            }
        }
    }
    return $galleries[$id] = $images;
}

function wisata_image(array $wisata): string
{
    $images = wisata_gallery((int) $wisata['wisata_id']);
    if ($images) {
        return base_url('uploads/wisata/' . $images[0]);
    }
    $filename = $wisata['gambar_wisata'] ?? '';
    if (is_string($filename) && preg_match('/\A[a-zA-Z0-9_-][a-zA-Z0-9_.-]*\.(?:jpg|jpeg|png|webp)\z/i', $filename) && is_file(FCPATH . 'uploads/wisata/' . $filename)) {
        return base_url('uploads/wisata/' . $filename);
    }
    return base_url('images/destination-placeholder.svg');
}

function youtube_embed(?string $url): string
{
    if (!safe_url($url)) {
        return '';
    }
    $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
    $path = parse_url($url, PHP_URL_PATH) ?? '';
    $id = '';
    if ($host === 'youtu.be') {
        $id = trim($path, '/');
    } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'www.youtube-nocookie.com'], true)) {
        if (preg_match('~^/(?:embed|shorts)/([A-Za-z0-9_-]{11})$~', $path, $matches)) {
            $id = $matches[1];
        } else {
            parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $query);
            $id = $query['v'] ?? '';
        }
    }
    return is_string($id) && preg_match('/\A[A-Za-z0-9_-]{11}\z/', $id) ? 'https://www.youtube-nocookie.com/embed/' . $id : '';
}

function visit_date(string $date): string
{
    return \CodeIgniter\I18n\Time::parse($date, 'Asia/Makassar', 'id_ID')->toLocalizedString('d MMMM yyyy');
}

function news_image(?string $value): string
{
    if ($url = safe_url($value ?? '')) return $url;
    if ($value && preg_match('/\A[a-zA-Z0-9_-][a-zA-Z0-9_.-]*\.(?:jpg|jpeg|png|webp)\z/i', $value) && !str_contains($value, '..')) return base_url('uploads/berita/' . $value);
    return base_url('images/destination-placeholder.svg');
}
