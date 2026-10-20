<footer class="mt-auto bg-[#063A2B] text-[#DCE8E1]">
    <div class="mx-auto w-full max-w-[1180px] px-4 pb-6 pt-14 sm:px-6 lg:px-8">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-12">
            <!-- Branding -->
            <section aria-label="Tentang Kebun Raya Mangrove Surabaya">
                <div class="flex items-center gap-3 lg:block">
                    <svg class="h-12 w-12 text-white" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                        <circle cx="24" cy="24" r="22" stroke="#91B8A5" stroke-width="1.5"/>
                        <path d="M14 32C13 20 24 13 35 13C35 26 28 34 18 34" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15 35C20 26 26 22 33 18" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    </svg>
                    <div class="lg:mt-2">
                        <p class="text-sm font-bold tracking-[0.12em] text-white">KEBUN RAYA</p>
                        <p class="mt-1 text-[10px] tracking-[0.16em]">MANGROVE SURABAYA</p>
                    </div>
                </div>
                <p class="mt-4 max-w-64 text-[13px] leading-6 text-[#BCD0C4]">
                    Ruang hijau, edukasi, dan rekreasi alam untuk semua.
                </p>
            </section>

            <!-- Jelajahi -->
            <nav aria-label="Jelajahi website">
                <h2 class="mb-4 text-xs font-semibold tracking-[0.2em] text-[#B2C8BA]">JELAJAHI</h2>
                <ul class="space-y-3 text-[13px] text-[#D0DFD5]">
                    <li><a href="{{ url('/') }}" class="hover:text-white hover:underline">Kebun Raya Mangrove</a></li>
                    <li><span title="Halaman sedang dikembangkan">Beli Tiket</span></li>
                    <li><span title="Halaman sedang dikembangkan">Reservasi Venue</span></li>
                    <li><span title="Halaman sedang dikembangkan">Berita</span></li>
                </ul>
            </nav>

            <!-- Wahana: teks hingga halaman detail tersedia -->
            <section aria-label="Wahana">
                <h2 class="mb-4 text-xs font-semibold tracking-[0.2em] text-[#B2C8BA]">WAHANA</h2>
                <ul class="space-y-3 text-[13px] text-[#D0DFD5]">
                    <li>Sepeda Air</li>
                    <li>ATV</li>
                    <li>Mobil Golf</li>
                    <li>Sepeda Listrik</li>
                </ul>
            </section>

            <!-- CATATAN: informasi kontak mengikuti mockup; verifikasi sebelum publikasi. -->
            <section aria-label="Kontak">
                <h2 class="mb-4 text-xs font-semibold tracking-[0.2em] text-[#B2C8BA]">KONTAK</h2>
                <address class="not-italic text-[13px] leading-6 text-[#D0DFD5]">
                    Jl. Mangrove Raya No. 1<br>
                    Gunung Anyar, Surabaya 60111<br>
                    (031) 123-4567<br>
                    info@krm.surabaya.go.id
                </address>
            </section>
        </div>

        <div class="mt-12 border-t border-[#356553] pt-6 text-center text-xs text-[#91B6A2]">
            &copy; {{ now()->year }} BRIDA Kota Surabaya. Semua hak dilindungi.
        </div>
    </div>
</footer>
