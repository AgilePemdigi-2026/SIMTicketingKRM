<?php

namespace App\Support;

class FigmaDemoContent
{
    public const ARTICLE_SLUG = 'contoh-figma-edukasi-mangrove';

    public static function enabled(): bool
    {
        return app()->environment('local') && (bool) config('figma_demo.enabled');
    }

    public static function gallery(): array
    {
        return array_map(fn (string $name): string => 'images/figma/'.$name.'.jpg',
            ['jembatan', 'lanskap', 'sungai', 'danau', 'lanskap', 'jembatan', 'danau', 'sungai']);
    }

    public static function article(): array
    {
        return [
            'slug' => self::ARTICLE_SLUG,
            'judul' => 'Cara Menjaga dan Melestarikan Ekosistem Mangrove Surabaya',
            'kategori' => 'Edukasi', 'tanggal' => 'Demo Proyek',
            'ringkasan' => 'Kenali langkah sederhana untuk menjaga kebersihan dan kelestarian lingkungan mangrove saat berkunjung.',
            'gambar' => 'images/figma/lanskap.jpg', 'penulis' => 'Demo Proyek', 'is_demo' => true,
            'isi' => "Artikel ini merupakan contoh konten untuk demonstrasi proyek, bukan publikasi resmi pengelola destinasi.\n\nMangrove menjadi tempat hidup beragam tumbuhan dan satwa. Saat berkunjung, gunakan jalur yang tersedia dan hindari merusak tanaman atau mengganggu satwa.\n\nBawa kembali sampahmu, gunakan tempat sampah yang tersedia, dan kurangi penggunaan plastik sekali pakai. Ikuti petunjuk Petugas agar kunjungan tetap nyaman bagi semua orang.\n\nPelestarian lingkungan dimulai dari kebiasaan sederhana. Ajak teman dan keluarga untuk menikmati kawasan mangrove dengan bertanggung jawab.",
        ];
    }
}
