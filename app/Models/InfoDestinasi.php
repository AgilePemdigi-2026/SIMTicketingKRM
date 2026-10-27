<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoDestinasi extends Model
{
    // Nama tabel PostgreSQL
    protected $table = 'info_destinasi';

    // Primary key sesuai migration
    protected $primaryKey = 'id_destinasi';

    // Kolom yang boleh diisi melalui Eloquent
    protected $fillable = [
        'nama_destinasi',
        'foto_utama',
        'deskripsi_singkat',
        'deskripsi_lengkap',
        'jam_buka',
        'jam_tutup',
        'alamat',
    ];
}