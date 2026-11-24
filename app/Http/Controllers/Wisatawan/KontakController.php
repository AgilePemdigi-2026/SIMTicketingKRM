<?php

namespace App\Http\Controllers\Wisatawan;

use App\Http\Controllers\Controller;
use App\Models\InfoKontak;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class KontakController extends Controller
{
    public function index(): View
    {
        // Informasi kontak berasal dari PostgreSQL.
        $kontak = InfoKontak::query()
            ->orderBy('id_kontak')
            ->first();

        return view('wisatawan.kontak', compact('kontak'));
    }

    public function kirimPesan(Request $request): RedirectResponse
    {
        // Validasi data yang dikirim wisatawan.
        $data = $request->validate([
            'nama' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:254'],
            'subjek' => ['required', 'string', 'min:3', 'max:150'],
            'pesan' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        // Untuk testing, kirim ke Gmail yang dikonfigurasi
        // pada MAIL_FROM_ADDRESS di file .env.
        $penerima = config('mail.from.address');

        $isiEmail = implode("\n", [
            'PESAN BARU DARI WEBSITE MANGROTIX',
            '',
            'Nama: ' . $data['nama'],
            'Email: ' . $data['email'],
            'Subjek: ' . $data['subjek'],
            '',
            'Isi Pesan:',
            $data['pesan'],
            '',
            '--------------------------------',
            'Dikirim melalui halaman Hubungi Kami MangroTix.',
        ]);

        try {
            Mail::raw($isiEmail, function ($message) use ($data, $penerima) {
                $message
                    ->to($penerima)
                    ->replyTo($data['email'], $data['nama'])
                    ->subject('MangroTix - Pesan Baru dari Wisatawan');
            });

            return redirect()
                ->route('wisatawan.kontak')
                ->with(
                    'contact_success',
                    'Pesan Anda berhasil dikirim. Terima kasih telah menghubungi kami.'
                );

        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with(
                    'contact_error',
                    'Pesan gagal dikirim. Silakan coba kembali.'
                );
        }
    }
}
