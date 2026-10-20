<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesiFasilitas extends Model
{
    protected $table = 'sesi_fasilitas';

    protected $primaryKey = 'id_sesi';

    public $timestamps = false;

    protected $fillable = [
        'id_fasilitas',
        'nama_sesi',
        'jam_mulai',
        'jam_selesai',
        'durasi_menit',
        'harga_sesi',
        'status_aktif',
    ];

    public function fasilitas(): BelongsTo
    {
        return $this->belongsTo(
            Fasilitas::class,
            'id_fasilitas',
            'id_fasilitas'
        );
    }
}
