# Pengujian Monitoring Operasional Harian

## 1. Informasi Pengujian

| Informasi | Keterangan |
| --- | --- |
| Nama Project | SIMTicketingKRM (MangroTix) |
| Modul | Monitoring Operasional Harian |
| Sprint | Sprint 3 |
| Penguji | Jessica Atalya Kriswianto |
| Tanggal Pengujian | 24 November 2026 |
| Branch | feature/sprint3-finishing-jessie |
| Environment | Local Development |
| Framework | Laravel |
| Database | PostgreSQL 17 |
| Payment Gateway | Midtrans Sandbox |
| Metode Pengujian | Manual End-to-End Testing dan Database Validation |
| Zona Waktu Operasional | Asia/Jakarta (WIB) |

## 2. Tujuan Pengujian

Pengujian dilakukan untuk memastikan fitur Monitoring
Operasional Harian dapat menampilkan statistik operasional
berdasarkan data aktual yang tersimpan dalam database
PostgreSQL.

Pengujian mencakup:

- Validasi jumlah transaksi tiket.
- Validasi jumlah pembayaran tiket lunas.
- Validasi total pendapatan tiket.
- Validasi penerbitan E-Tiket.
- Validasi jumlah pengunjung yang telah check-in.
- Validasi pencegahan penggunaan E-Tiket secara berulang.
- Validasi jumlah reservasi fasilitas.
- Validasi integrasi pembayaran Cash dan QRIS.
- Validasi integrasi pembayaran reservasi fasilitas.
- Validasi batas waktu operasional berdasarkan WIB.
- Validasi konsistensi data antara Dashboard Admin,
  Dashboard Petugas, dan database PostgreSQL.

## 3. Komponen Sistem yang Diuji

### 3.1 Backend

- AdminDashboardController.php
- PetugasDashboardController.php
- TicketOrder.php
- TicketPayment.php
- IssuedTicket.php
- FacilityReservation.php
- FacilityReservationPayment.php

### 3.2 Frontend

- resources/views/admin/dashboard.blade.php
- resources/views/petugas/ticket-payment/dashboard.blade.php

### 3.3 Database

- ticket_orders
- ticket_payments
- issued_tickets
- facility_reservations
- facility_reservation_payments

### 3.4 Modul Integrasi

- Pembelian tiket online oleh Wisatawan.
- Pembayaran Cash Booking oleh Petugas.
- Pembayaran QRIS melalui Midtrans Sandbox.
- Penerbitan E-Tiket sementara dan final.
- Validasi dan check-in E-Tiket.
- Reservasi fasilitas oleh Wisatawan.
- Pembayaran reservasi melalui QRIS.
- Penerbitan invoice reservasi PDF.

## 4. Kondisi Awal Database

Sebelum pengujian dilakukan, database PostgreSQL lokal
memiliki data sebagai berikut.

| Data | Jumlah |
| --- | ---: |
| Pesanan tiket | 4 |
| Pembayaran tiket | 4 |
| Pembayaran tiket lunas | 4 |
| Total pendapatan tiket | Rp200.000 |
| E-Tiket diterbitkan | 2 |
| E-Tiket berstatus active | 1 |
| E-Tiket berstatus used | 1 |
| Reservasi fasilitas | 0 |
| Pembayaran reservasi lunas | 0 |
| Total pendapatan reservasi | Rp0 |

Seluruh transaksi sebelumnya dilakukan sebelum tanggal
24 November 2026 sehingga tidak termasuk dalam periode
Monitoring Operasional Harian pada tanggal pengujian.

### 4.1 Kondisi Awal Dashboard Admin

| Indikator | Nilai Awal |
| --- | ---: |
| Transaksi Tiket | 0 |
| Pembayaran Lunas | 0 |
| Pendapatan Tiket | Rp0 |
| Pengunjung Check-in | 0 |
| Reservasi Fasilitas | 0 |

### 4.2 Kondisi Awal Dashboard Petugas

| Indikator | Nilai Awal |
| --- | ---: |
| Pengunjung Hari Ini | 0 |
| Sudah Check-in | 0 |
| Cash Booking Belum Bayar | 0 |
| Transaksi Hari Ini | 0 |
| Reservasi Hari Ini | 0 |

Statistik awal bernilai nol karena belum terdapat aktivitas
yang memenuhi kriteria periode operasional pada tanggal
24 November 2026.

## 5. Validasi Zona Waktu

Aplikasi menggunakan UTC sebagai zona waktu penyimpanan
timestamp dan Asia/Jakarta sebagai zona waktu operasional.

Hasil pemeriksaan konfigurasi melalui Laravel Tinker:

| Parameter | Hasil |
| --- | --- |
| Application Timezone | UTC |
| Operational Timezone | Asia/Jakarta |
| Selisih Zona Waktu | UTC+7 |
| Awal Periode WIB | 24 November 2026, 00:00 |
| Akhir Periode WIB | 25 November 2026, 00:00 |
| Awal Periode UTC | 23 November 2026, 17:00 |
| Akhir Periode UTC | 24 November 2026, 17:00 |

Controller menggunakan batas awal inklusif dan batas
akhir eksklusif dalam menentukan periode statistik harian.

Pendekatan tersebut memastikan transaksi pada pergantian
hari tidak dihitung pada dua periode operasional sekaligus.

Validasi dilakukan dengan membandingkan waktu aplikasi,
waktu operasional WIB, dan batas periode UTC melalui
Laravel Tinker.

Hasil validasi konfigurasi zona waktu: LULUS.

Pengujian otomatis terhadap transaksi yang berada tepat
pada batas awal dan akhir periode masih perlu dilakukan.

## 6. Pengujian Pembelian Tiket Cash Booking

### 6.1 Data Pengujian

| Parameter | Keterangan |
| --- | --- |
| Kode Pembayaran | CASH-0005 |
| Tanggal Kunjungan | 24 November 2026 |
| Jenis Tiket | Tiket Reguler - Dewasa |
| Jumlah Tiket | 1 |
| Metode Pembayaran | Cash |
| Total Pembayaran | Rp50.000 |

### 6.2 Alur Pengujian

1. Wisatawan melakukan pemesanan tiket online.
2. Wisatawan memilih metode pembayaran Cash Booking.
3. Sistem menerbitkan E-Tiket sementara.
4. Admin memeriksa perubahan statistik transaksi.
5. Petugas mencari kode pembayaran Cash Booking.
6. Petugas melakukan konfirmasi pembayaran tunai.
7. Sistem memperbarui status pembayaran menjadi lunas.
8. E-Tiket final tersedia untuk proses validasi.
9. Petugas melakukan validasi dan check-in E-Tiket.
10. Admin dan Petugas memeriksa statistik operasional.

### 6.3 Hasil Dashboard Admin

| Indikator | Sebelum Pemesanan | Sebelum Pembayaran | Setelah Check-in |
| --- | ---: | ---: | ---: |
| Transaksi Tiket | 0 | 1 | 1 |
| Pembayaran Lunas | 0 | 0 | 1 |
| Pendapatan Tiket | Rp0 | Rp0 | Rp50.000 |
| Pengunjung Check-in | 0 | 0 | 1 |
| Reservasi Fasilitas | 0 | 0 | 0 |

### 6.4 Hasil Dashboard Petugas

| Indikator | Sebelum Pemesanan | Sebelum Pembayaran | Setelah Check-in |
| --- | ---: | ---: | ---: |
| Pengunjung Hari Ini | 0 | 0 | 1 |
| Sudah Check-in | 0 | 0 | 1 |
| Cash Booking Belum Bayar | 0 | 1 | 0 |
| Transaksi Hari Ini | 0 | 1 | 1 |
| Reservasi Hari Ini | 0 | 0 | 0 |

Sistem berhasil membedakan pesanan yang belum dibayar
dengan pesanan yang telah lunas.

Pesanan yang belum dibayar tidak dihitung sebagai
pendapatan tiket.

Setelah pembayaran dikonfirmasi, pendapatan tiket
bertambah sesuai nominal transaksi.

Hasil pengujian: LULUS.

## 7. Pengujian Validasi dan Check-in E-Tiket

Pengujian dilakukan menggunakan E-Tiket final yang
diterbitkan setelah pembayaran Cash Booking berhasil
dikonfirmasi.

### 7.1 Hasil Check-in

| Parameter | Hasil |
| --- | --- |
| Jenis Tiket | Tiket Reguler |
| Jumlah Tiket | 1 |
| Status Pembayaran | Lunas |
| Waktu Check-in | 24 November 2026, 14:56 WIB |
| Petugas | Petugas Testing |
| Status Akhir | Sudah Check-in |

Sistem berhasil menampilkan informasi pengunjung,
jenis tiket, waktu check-in, dan nama Petugas.

Jumlah pengunjung check-in pada Dashboard Admin
dan Dashboard Petugas bertambah menjadi satu.

### 7.2 Pengujian Validasi Berulang

Kode tiket yang telah digunakan dimasukkan kembali
ke halaman validasi tiket.

Hasil aktual:

- Sistem berhasil menemukan informasi tiket.
- Status pembayaran tetap lunas.
- Status tiket menunjukkan sudah digunakan.
- Sistem menampilkan status tidak valid.
- Sistem menampilkan pesan bahwa E-Tiket sudah
  digunakan atau tidak aktif.
- Jumlah pengunjung check-in pada Dashboard Admin
  tetap satu.

Pengujian menunjukkan bahwa validasi berulang melalui
antarmuka tidak menambah jumlah pengunjung check-in.

Pengujian konkurensi dan pengiriman ulang request
check-in secara langsung belum dilakukan.

Hasil pengujian manual: LULUS.

## 8. Pengujian Pembelian Tiket QRIS

### 8.1 Data Pengujian

| Parameter | Keterangan |
| --- | --- |
| Jenis Transaksi | Pembelian Tiket Online |
| Metode Pembayaran | QRIS |
| Payment Gateway | Midtrans Sandbox |
| Total Pembayaran | Rp50.000 |
| Status Pembayaran | Lunas |

### 8.2 Alur Pengujian

1. Wisatawan melakukan pembelian tiket online.
2. Wisatawan memilih metode pembayaran QRIS.
3. Sistem menghasilkan kode QRIS pembayaran.
4. Pembayaran diselesaikan melalui Midtrans Sandbox.
5. Sistem memperbarui status pembayaran menjadi lunas.
6. Admin melakukan refresh halaman dashboard.
7. Statistik dibandingkan dengan transaksi sebelumnya.

### 8.3 Hasil Dashboard Admin

| Indikator | Sebelum QRIS | Setelah QRIS |
| --- | ---: | ---: |
| Transaksi Tiket | 1 | 2 |
| Pembayaran Lunas | 1 | 2 |
| Pendapatan Tiket | Rp50.000 | Rp100.000 |
| Pengunjung Check-in | 1 | 1 |
| Reservasi Fasilitas | 0 | 0 |

Pembayaran QRIS berhasil menambah jumlah transaksi,
jumlah pembayaran lunas, dan total pendapatan tiket.

Jumlah pengunjung check-in tetap satu karena belum
terdapat aktivitas check-in tambahan.

Hasil pengujian: LULUS.

## 9. Pengujian Reservasi Fasilitas

### 9.1 Data Pengujian

| Parameter | Keterangan |
| --- | --- |
| Nomor Invoice | RSV-20261124-MBMBX3YV |
| Nama Fasilitas | Gazebo Uji Coba |
| Tanggal Penggunaan | 24 November 2026 |
| Sesi Penggunaan | 08:00-10:00 WIB |
| Jumlah Peserta | 10 |
| Tarif per Peserta | Rp50.000 |
| Total Pembayaran | Rp500.000 |
| Metode Pembayaran | QRIS |
| Payment Gateway | Midtrans Sandbox |
| Status Pembayaran | paid |
| Status Administratif | pending |

### 9.2 Alur Pengujian

1. Wisatawan memilih fasilitas yang tersedia.
2. Wisatawan menentukan tanggal dan sesi penggunaan.
3. Wisatawan melengkapi data reservasi.
4. Sistem berhasil membuat data reservasi.
5. Wisatawan membuat QRIS pembayaran.
6. Pembayaran diselesaikan melalui Midtrans Sandbox.
7. Sistem memperbarui status pembayaran menjadi lunas.
8. Sistem mengaktifkan akses invoice reservasi.
9. Invoice reservasi PDF berhasil diterbitkan.
10. Data reservasi dan pembayaran diverifikasi
    melalui Laravel Tinker.
11. Statistik AdminDashboardController diverifikasi.

### 9.3 Hasil Pengujian Reservasi

| Komponen | Hasil |
| --- | --- |
| Pembuatan Reservasi | LULUS |
| Pembuatan Pembayaran QRIS | LULUS |
| Konfirmasi Pembayaran | LULUS |
| Penerbitan Invoice PDF | LULUS |
| Penyimpanan Data Reservasi | LULUS |
| Penyimpanan Data Pembayaran | LULUS |
| Perhitungan Reservasi pada Controller Admin | LULUS |
| Tampilan Akhir Dashboard Petugas | BELUM DIVERIFIKASI |

### 9.4 Validasi Status Reservasi

Status pembayaran dan status administratif merupakan
dua status yang berbeda dalam proses reservasi.

Pembayaran memiliki status paid karena pembayaran
telah berhasil diverifikasi melalui Midtrans Sandbox.

Reservasi memiliki status administratif pending karena
masih menunggu proses pengelolaan oleh Admin.

Kondisi tersebut sesuai dengan data yang ditemukan
melalui Laravel Tinker dan keterangan pada invoice.

Controller Admin menghitung reservasi berdasarkan
tanggal penggunaan tanpa membatasi status administratif.

Controller Petugas menghitung reservasi terjadwal
yang telah memiliki pembayaran berstatus paid.

## 10. Validasi Akhir Dashboard Admin

Validasi dilakukan melalui Laravel Tinker dengan
menjalankan method index pada AdminDashboardController.

Perintah yang digunakan:

    app(App\Http\Controllers\Admin\AdminDashboardController::class)->index()->getData();

Hasil aktual:

    [
        "stats" => [
            "transaksi_tiket" => 2,
            "pembayaran_lunas" => 2,
            "pendapatan_tiket" => "100000.00",
            "pengunjung_checkin" => 1,
            "reservasi_hari_ini" => 1,
        ],
    ]

Perbandingan hasil:

| Indikator | Ekspektasi | Aktual | Status |
| --- | ---: | ---: | --- |
| Transaksi Tiket | 2 | 2 | LULUS |
| Pembayaran Lunas | 2 | 2 | LULUS |
| Pendapatan Tiket | Rp100.000 | Rp100.000 | LULUS |
| Pengunjung Check-in | 1 | 1 | LULUS |
| Reservasi Fasilitas | 1 | 1 | LULUS |

Seluruh statistik yang dihasilkan AdminDashboardController
sesuai dengan ekspektasi berdasarkan transaksi pengujian
pada tanggal 24 November 2026.

Validasi akhir ini membuktikan hasil perhitungan backend.
Pemeriksaan visual kedua dashboard setelah reservasi
masih perlu dilengkapi.

## 11. Validasi Akhir Database PostgreSQL

### 11.1 Data Reservasi

Hasil pemeriksaan model FacilityReservation:

| Atribut | Nilai |
| --- | --- |
| id | 1 |
| invoice_number | RSV-20261124-MBMBX3YV |
| visit_date | 2026-11-24 |
| total_amount | 500000.00 |
| administrative_status | pending |

### 11.2 Data Pembayaran Reservasi

Hasil pemeriksaan model FacilityReservationPayment:

| Atribut | Nilai |
| --- | --- |
| id | 1 |
| facility_reservation_id | 1 |
| method | qris |
| status | paid |
| amount | 500000.00 |
| paid_at UTC | 2026-11-24 08:09:41 |
| paid_at WIB | 2026-11-24 15:09:41 |

Data pembayaran berhasil terhubung dengan data reservasi
melalui facility_reservation_id.

### 11.3 Validasi Pendapatan Tiket

Perintah Laravel Tinker:

    App\Models\TicketPayment::where('status', 'paid')->sum('amount');

Hasil:

    "300000.00"

Total pendapatan tiket seluruh periode adalah Rp300.000.

### 11.4 Validasi Pendapatan Reservasi

Perintah Laravel Tinker:

    App\Models\FacilityReservationPayment::where('status', 'paid')->sum('amount');

Hasil:

    "500000.00"

Total pendapatan reservasi seluruh periode adalah Rp500.000.

## 12. Rekapitulasi Pendapatan

### 12.1 Pendapatan Harian Pengujian

| Jenis Transaksi | Metode Pembayaran | Nominal | Status |
| --- | --- | ---: | --- |
| Pembelian Tiket | Cash | Rp50.000 | Lunas |
| Pembelian Tiket | QRIS | Rp50.000 | Lunas |
| Reservasi Fasilitas | QRIS | Rp500.000 | Lunas |
| Total | | Rp600.000 | |

Ringkasan pendapatan harian:

- Pendapatan tiket Cash: Rp50.000.
- Pendapatan tiket QRIS: Rp50.000.
- Total pendapatan tiket: Rp100.000.
- Total pendapatan reservasi: Rp500.000.
- Total pendapatan pengujian: Rp600.000.

### 12.2 Pendapatan Seluruh Periode

| Kategori | Sebelum Pengujian | Setelah Pengujian |
| --- | ---: | ---: |
| Pendapatan Tiket | Rp200.000 | Rp300.000 |
| Pendapatan Reservasi | Rp0 | Rp500.000 |
| Total Pendapatan | Rp200.000 | Rp800.000 |

Pendapatan reservasi tidak dicampurkan ke dalam indikator
Pendapatan Tiket pada Dashboard Admin karena keduanya
menggunakan kategori transaksi dan sumber data yang berbeda.

Data tersebut dapat digunakan sebagai acuan awal untuk
pengembangan dan pengujian fitur Laporan Operasional.

## 13. Rekapitulasi Test Case

| ID | Skenario Pengujian | Hasil Aktual | Status |
| --- | --- | --- | --- |
| MON-01 | Koneksi PostgreSQL | Seluruh query model berhasil dijalankan | LULUS |
| MON-02 | Dashboard tanpa transaksi hari ini | Seluruh statistik awal bernilai nol | LULUS |
| MON-03 | Validasi konfigurasi timezone | UTC dan WIB sesuai | LULUS |
| MON-04 | Pembelian tiket Cash Booking | Transaksi bertambah menjadi 1 | LULUS |
| MON-05 | Penerbitan E-Tiket sementara | E-Tiket sementara tersedia | LULUS |
| MON-06 | Monitoring Cash Booking belum bayar | Pendapatan tetap Rp0 | LULUS |
| MON-07 | Monitoring Cash Booking Petugas | Cash Booking belum bayar bertambah menjadi 1 | LULUS |
| MON-08 | Konfirmasi pembayaran Cash | Pembayaran menjadi lunas | LULUS |
| MON-09 | Perhitungan pendapatan Cash | Pendapatan menjadi Rp50.000 | LULUS |
| MON-10 | Penerbitan E-Tiket final | E-Tiket dapat digunakan untuk check-in | LULUS |
| MON-11 | Validasi dan check-in tiket | Check-in berhasil | LULUS |
| MON-12 | Pencatatan waktu check-in | Waktu WIB ditampilkan | LULUS |
| MON-13 | Identifikasi Petugas check-in | Nama Petugas ditampilkan | LULUS |
| MON-14 | Monitoring check-in Admin | Jumlah check-in menjadi 1 | LULUS |
| MON-15 | Monitoring check-in Petugas | Jumlah check-in menjadi 1 | LULUS |
| MON-16 | Validasi ulang E-Tiket | Tiket yang telah digunakan ditolak | LULUS |
| MON-17 | Pencegahan double counting secara manual | Jumlah check-in tetap 1 | LULUS |
| MON-18 | Pembelian tiket QRIS | Transaksi berhasil dibuat | LULUS |
| MON-19 | Konfirmasi pembayaran QRIS | Status pembayaran lunas | LULUS |
| MON-20 | Akumulasi pendapatan Cash dan QRIS | Pendapatan menjadi Rp100.000 | LULUS |
| MON-21 | Pembuatan reservasi fasilitas | Reservasi berhasil dibuat | LULUS |
| MON-22 | Pembayaran reservasi QRIS | Pembayaran Rp500.000 berhasil | LULUS |
| MON-23 | Penerbitan invoice reservasi | Invoice PDF berhasil diterbitkan | LULUS |
| MON-24 | Penyimpanan data reservasi | Data ditemukan pada PostgreSQL | LULUS |
| MON-25 | Penyimpanan pembayaran reservasi | Status pembayaran paid | LULUS |
| MON-26 | Validasi status administratif | Status pending ditemukan | LULUS |
| MON-27 | Monitoring reservasi Controller Admin | Jumlah reservasi menjadi 1 | LULUS |
| MON-28 | Validasi total pendapatan tiket | Rp300.000 seluruh periode | LULUS |
| MON-29 | Validasi total pendapatan reservasi | Rp500.000 seluruh periode | LULUS |
| MON-30 | Validasi statistik akhir Controller Admin | Seluruh statistik sesuai ekspektasi | LULUS |
| MON-31 | Validasi visual dashboard setelah reservasi | Bukti akhir belum diperiksa | PENDING |
| MON-32 | Automated testing monitoring | Belum dilaksanakan | PENDING |

### 13.1 Ringkasan Hasil

| Status | Jumlah Test Case |
| --- | ---: |
| LULUS | 30 |
| GAGAL | 0 |
| PENDING | 2 |
| Total | 32 |

Hasil tersebut menunjukkan bahwa skenario utama monitoring
yang telah diuji secara manual dan melalui validasi
database berjalan sesuai dengan ekspektasi.

## 14. Temuan Pengujian Integrasi

### 14.1 Pemilihan Sesi Reservasi yang Telah Berlalu

Pada pengujian reservasi ditemukan kondisi berikut:

- Tanggal reservasi: 24 November 2026.
- Sesi penggunaan: 08:00-10:00 WIB.
- Waktu pembayaran: 15:09 WIB.

Sistem mengizinkan proses reservasi dan pembayaran
untuk sesi yang waktunya telah berlalu.

Kondisi tersebut merupakan potensi masalah validasi
jadwal reservasi yang perlu dikonfirmasi terhadap
aturan bisnis pada Software Requirements Specification.

Temuan ini perlu dikomunikasikan kepada pengembang
modul reservasi sebelum dilakukan perubahan kode.

### 14.2 Data Historis E-Tiket

Database lokal memiliki dua pesanan historis dengan
status paid yang belum memiliki E-Tiket.

Kondisi tersebut ditemukan sebelum pelaksanaan
pengujian tanggal 24 November 2026.

Penyebab pasti belum dikonfirmasi. Data tersebut
kemungkinan berasal dari pengujian pada tahap
pengembangan fitur sebelumnya.

Tidak dilakukan perubahan maupun penghapusan terhadap
data historis tersebut.

### 14.3 Perbedaan Definisi Statistik

Dashboard Admin menghitung transaksi berdasarkan
waktu pembuatan pesanan tiket.

Dashboard Petugas menghitung transaksi berdasarkan
waktu pembuatan data pembayaran tiket.

Dashboard Admin menghitung seluruh reservasi yang
terjadwal pada tanggal operasional.

Dashboard Petugas menghitung reservasi terjadwal
yang memiliki pembayaran berstatus paid.

Perbedaan definisi tersebut perlu dievaluasi terhadap
kebutuhan masing-masing aktor pada SRS.

### 14.4 Kesesuaian Antarmuka Admin

Tampilan Dashboard Admin saat ini telah menampilkan
statistik operasional berdasarkan data aktual.

Namun, penyesuaian desain dengan Data Mock Up masih
perlu dilakukan pada tahap finalisasi Sprint 3.

Penyesuaian mencakup:

- Struktur navigasi sidebar.
- Tampilan header dan topbar.
- Tata letak kartu statistik.
- Penggunaan ikon.
- Konsistensi warna dan tipografi.
- Tata letak tabel dan filter.
- Konsistensi tombol dan komponen antarmuka.
- Responsivitas halaman Admin.

## 15. Kesimpulan

Berdasarkan pengujian manual dan validasi database
PostgreSQL, fitur Monitoring Operasional Harian Admin
telah berhasil menampilkan statistik transaksi tiket,
pembayaran lunas, pendapatan tiket, check-in pengunjung,
dan reservasi fasilitas berdasarkan data aktual.

Integrasi pembayaran Cash dan QRIS Midtrans Sandbox
berhasil memperbarui statistik transaksi dan pendapatan.

Sistem berhasil membedakan transaksi yang belum dibayar
dengan transaksi yang telah lunas.

Proses penerbitan E-Tiket, validasi, dan check-in berhasil
terintegrasi dengan statistik Monitoring Operasional
Harian pada skenario pengujian.

Validasi ulang E-Tiket yang telah digunakan berhasil
ditolak melalui antarmuka tanpa menambah jumlah
pengunjung check-in.

Pembuatan reservasi, pembayaran QRIS, dan penerbitan
invoice reservasi berhasil dilakukan.

Hasil akhir AdminDashboardController menunjukkan:

- Transaksi tiket: 2.
- Pembayaran tiket lunas: 2.
- Pendapatan tiket: Rp100.000.
- Pengunjung check-in: 1.
- Reservasi fasilitas: 1.

Seluruh statistik backend Admin sesuai dengan ekspektasi.

Total pendapatan pengujian harian adalah Rp600.000,
sedangkan akumulasi pendapatan tiket dan reservasi
seluruh periode pada database lokal adalah Rp800.000.

Konfigurasi UTC dan Asia/Jakarta telah sesuai dengan
periode operasional harian yang digunakan.

Pengujian otomatis, pemeriksaan batas waktu query,
validasi pembatasan akses, dan finalisasi antarmuka
masih perlu dilakukan sebelum Sprint 3 dinyatakan
selesai secara keseluruhan.

## 16. Rencana Pengembangan Selanjutnya

- Melengkapi bukti visual dashboard setelah reservasi.
- Membuat automated testing Monitoring Admin.
- Menguji batas periode operasional UTC dan WIB.
- Menguji pembatasan akses berdasarkan role.
- Mengembangkan Audit Log Aktivitas Sistem.
- Mengintegrasikan pencatatan aktivitas pembayaran.
- Mengintegrasikan pencatatan aktivitas check-in.
- Mengintegrasikan pencatatan perubahan status reservasi.
- Mengembangkan Laporan Operasional.
- Mengimplementasikan filter periode laporan.
- Mengimplementasikan ekspor laporan PDF dan Excel.
- Melakukan finalisasi UI Admin sesuai Data Mock Up.
- Melakukan integration testing dan regression testing.
- Melengkapi Behavior Driven Development Testing.
- Menyiapkan dokumentasi akhir Sprint 3.
- Melakukan Pull Request ke branch develop.
