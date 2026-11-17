<?php

namespace App\Http\Controllers\Wisatawan;

use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use Illuminate\Http\Request;

class ReservasiPageController extends Controller
{
    public function index()
    {
        // Ambil fasilitas aktif yang memiliki sesi aktif.
        $fasilitas = Fasilitas::query()
            ->where('status_fasilitas', 1)
            ->whereHas('sesiFasilitas', function ($query) {
                $query->where('status_aktif', 1);
            })
            ->with(['sesiFasilitas' => function ($query) {
                $query->where('status_aktif', 1)
                    ->orderBy('harga_sesi', 'asc');
            }])
            ->orderBy('nama_fasilitas', 'asc')
            ->get();

        return view(
            'wisatawan.reservasi.index',
            compact('fasilitas')
        );
    }

    public function tanggal(Fasilitas $fasilitas)
    {
        // Fasilitas harus aktif.
        abort_unless(
            (int) $fasilitas->status_fasilitas === 1,
            404
        );

        // Ambil sesi aktif dari fasilitas terpilih.
        $sesiAktif = $fasilitas->sesiFasilitas()
            ->where('status_aktif', 1)
            ->orderBy('jam_mulai')
            ->get();

        abort_if($sesiAktif->isEmpty(), 404);

        return view('wisatawan.reservasi.tanggal', [
            'fasilitas' => $fasilitas,
            'sesiAktif' => $sesiAktif,
        ]);
    }

    public function data(Request $request, Fasilitas $fasilitas)
    {
        // Pastikan fasilitas masih aktif.
        abort_unless(
            (int) $fasilitas->status_fasilitas === 1,
            404
        );

        // Validasi tanggal dan ID sesi dari halaman sebelumnya.
        $validated = $request->validate([
            'tanggal' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'sesi' => [
                'required',
                'integer',
            ],
        ]);

        // Pastikan sesi aktif dan benar-benar milik fasilitas ini.
        $sesi = $fasilitas->sesiFasilitas()
            ->where('status_aktif', 1)
            ->where('id_sesi', $validated['sesi'])
            ->firstOrFail();

        return view('wisatawan.reservasi.data', [
            'fasilitas' => $fasilitas,
            'sesi' => $sesi,
            'tanggal' => $validated['tanggal'],
        ]);
    }

    public function previewPembayaran(
        \Illuminate\Http\Request $request,
        \App\Models\Fasilitas $fasilitas
    ) {
        abort_unless(
            (int) $fasilitas->status_fasilitas === 1,
            404
        );

        $validated = $request->validate([
            'tanggal' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'sesi' => [
                'required',
                'integer',
            ],
        ]);

        $sesi = $fasilitas->sesiFasilitas()
            ->where('status_aktif', 1)
            ->where('id_sesi', $validated['sesi'])
            ->firstOrFail();

        return view('wisatawan.reservasi.pembayaran', [
            'fasilitas' => $fasilitas,
            'sesi' => $sesi,
            'tanggal' => $validated['tanggal'],
        ]);
    }

    public function previewInvoice(
        \Illuminate\Http\Request $request,
        \App\Models\Fasilitas $fasilitas
    ) {
        // Pastikan fasilitas masih aktif
        abort_unless(
            (int) $fasilitas->status_fasilitas === 1,
            404
        );

        // Validasi pilihan reservasi
        $validated = $request->validate([
            'tanggal' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'sesi' => [
                'required',
                'integer',
            ],
        ]);

        // Pastikan sesi milik fasilitas yang dipilih
        $sesi = $fasilitas->sesiFasilitas()
            ->where('status_aktif', 1)
            ->where('id_sesi', $validated['sesi'])
            ->firstOrFail();

        return view('wisatawan.reservasi.invoice', [
            'fasilitas' => $fasilitas,
            'sesi' => $sesi,
            'tanggal' => $validated['tanggal'],
        ]);
    }

    public function previewReschedule(
        \Illuminate\Http\Request $request,
        \App\Models\Fasilitas $fasilitas
    ) {
        abort_unless(
            (int) $fasilitas->status_fasilitas === 1,
            404
        );

        // Jadwal acuan untuk pengujian UI.
        // Belum berasal dari reservasi sungguhan.
        $validated = $request->validate([
            'tanggal' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'sesi' => [
                'required',
                'integer',
            ],
        ]);

        $sesiSaatIni = $fasilitas->sesiFasilitas()
            ->where('status_aktif', 1)
            ->where('id_sesi', $validated['sesi'])
            ->firstOrFail();

        $sesiAktif = $fasilitas->sesiFasilitas()
            ->where('status_aktif', 1)
            ->orderBy('jam_mulai')
            ->get();

        return view('wisatawan.reservasi.reschedule', [
            'fasilitas' => $fasilitas,
            'sesiSaatIni' => $sesiSaatIni,
            'sesiAktif' => $sesiAktif,
            'tanggalSaatIni' => $validated['tanggal'],
        ]);
    }

}
