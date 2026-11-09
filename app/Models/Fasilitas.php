<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fasilitas extends Model
{
    protected $table = 'fasilitas';

    protected $primaryKey = 'id_fasilitas';

    public $timestamps = false;

    protected $fillable = [
        'nama_fasilitas',
        'kapasitas_fasilitas',
        'status_fasilitas',
        'ketentuan_fasilitas',
        'deskripsi_fasilitas',
        'foto_fasilitas',
    ];

    public function sesiFasilitas(): HasMany
    {
        return $this->hasMany(
            SesiFasilitas::class,
            'id_fasilitas',
            'id_fasilitas'
        );
    }
}
