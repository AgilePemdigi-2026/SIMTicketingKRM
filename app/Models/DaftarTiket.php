<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DaftarTiket extends Model
{
    protected $table = 'daftar_tiket';

    protected $primaryKey = 'id_tiket';

    public $timestamps = false;

    protected $fillable = [
        'id_wahana',
        'nama_tiket',
        'kategori_tiket',
        'kuota',
        'kategori_wisatawan',
        'harga_dewasa',
        'harga_anak',
        'status_aktif',
        'ketentuan',
    ];

    public function wahana(): BelongsTo
    {
        return $this->belongsTo(Wahana::class, 'id_wahana', 'id_wahana');
    }

    public function daftarWahana(): BelongsToMany
    {
    return $this->belongsToMany(
        Wahana::class,
        'tiket_wahana',
        'id_tiket',
        'id_wahana',
        'id_tiket',
        'id_wahana'
    );
    }
}
