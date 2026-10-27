<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoKontak extends Model
{
    // Nama tabel PostgreSQL
    protected $table = 'info_kontak';

    // Primary key sesuai migration
    protected $primaryKey = 'id_kontak';

    // Kolom yang boleh diisi melalui Eloquent
    protected $fillable = [
        'alamat',
        'nomor_telepon',
        'email',
        'jam_operasional',
        'google_maps_url',
        'instagram',
        'facebook',
        'youtube',
        'twitter',
    ];
}