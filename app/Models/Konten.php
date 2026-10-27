<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Konten extends Model
{
    // Nama tabel PostgreSQL
    protected $table = 'konten';

    // Primary key sesuai migration
    protected $primaryKey = 'id_konten';

    // Kolom yang boleh diisi melalui Eloquent
    protected $fillable = [
        'id_pengguna',
        'jenis_konten',
        'judul',
        'slug',
        'kategori',
        'isi',
        'penulis',
        'gambar',
        'waktu_publikasi',
        'status_publikasi',
    ];

    // Konversi tipe data
    protected function casts(): array
    {
        return [
            'waktu_publikasi' => 'datetime',
        ];
    }

    // Relasi ke pengguna yang membuat konten
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_pengguna',
            'id'
        );
    }
}