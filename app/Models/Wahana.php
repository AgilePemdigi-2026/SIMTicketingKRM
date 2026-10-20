<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Wahana extends Model
{
    protected $table = 'wahana';

    protected $primaryKey = 'id_wahana';

    public $timestamps = false;

    protected $fillable = [
        'nama_wahana',
        'durasi',
        'jam_buka',
        'jam_tutup',
        'kapasitas',
        'status_aktif',
        'deskripsi',
        'foto',
    ];

    public function daftarTiket(): BelongsToMany
    {
        return $this->belongsToMany(
            DaftarTiket::class,
            'tiket_wahana',
            'id_wahana',
            'id_tiket',
            'id_wahana',
            'id_tiket'
        );
    }
}
