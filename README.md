# SIAK Intel

SIAK Intel adalah aplikasi lokal untuk mengelola Laporan Intelijen, kegiatan, surat perintah, dan pengingat tenggat. Gunakan data fiktif selama pengembangan. Aplikasi ini belum disetujui untuk menerima dokumen intelijen atau dokumen instansi yang sebenarnya.

## Teknologi dan struktur

- PHP 8.2+, Laravel 12, Filament 5, Livewire 4, Tailwind CSS 4, Vite, dan MySQL/MariaDB.
- Panel administrasi: `/admin`.
- Berkas laporan berada di private disk `archive_private` pada `storage/app/private/archives`. Jangan membuat symlink publik untuk direktori ini.
- Laporan menyimpan metadata dan versi berkas terpisah. Setiap unggahan memakai nama penyimpanan acak; penghapusan metadata memakai soft delete dan tidak menghapus berkas fisik.
- Hak akses memakai role dan permission. Role bawaan: Administrator Sistem, Petugas Laporan, dan Pengguna Baca. Akun awal tidak dibuat oleh seeder.
- Tidak ada email, WhatsApp, cloud storage, ataupun layanan eksternal yang digunakan untuk mengirim data.

## Menyiapkan di Windows dan XAMPP

Perintah berikut mengacu pada workspace `C:\kejaksaan` dan XAMPP `C:\xampp62`. Sesuaikan path bila instalasi lokal berbeda. Jalankan MySQL dari XAMPP Control Panel.

```powershell
Set-Location C:\kejaksaan
$env:PHPRC = 'C:\xampp62\php'
$env:PHP_INI_SCAN_DIR = ''
$php = 'C:\xampp62\php\php.exe'

# Buat database development jika belum tersedia. Jangan memakai database produksi.
& 'C:\xampp62\mysql\bin\mysql.exe' --host=127.0.0.1 --user=root -e "CREATE DATABASE IF NOT EXISTS siak_intel_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# .env yang sudah ada tidak ditimpa. Periksa koneksi MySQL dan key aplikasi di sana.
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
composer install
& $php artisan migrate
& $php artisan db:seed
npm.cmd install
npm.cmd run build
& $php artisan siak:make-admin
& $php artisan serve
```

Buka `http://127.0.0.1:8000/admin`. `siak:make-admin` meminta nama, email, dan kata sandi secara interaktif; gunakan kata sandi unik minimal 12 karakter. Jangan menyimpan kredensial dalam source control. Untuk Apache XAMPP, arahkan DocumentRoot/VirtualHost ke direktori `public`, bukan root proyek.

Pengguna dapat mengubah kata sandi dari menu profil dengan memasukkan kata sandi saat ini. MFA aplikasi authenticator (TOTP) tersedia secara opsional dari profil; simpan recovery code di tempat aman. Secret MFA dan recovery code dienkripsi memakai `APP_KEY`, sehingga kunci yang sama harus dipertahankan saat pemulihan backup. MFA belum diwajibkan; putuskan kebijakan kewajibannya sebelum penggunaan resmi.

Seeder hanya membuat kategori generik, subjenis generik, aturan pengingat, jenis surat contoh, role, dan permission. Seeder tidak membuat user atau mengisi kalender dengan hari libur yang belum dikonfirmasi.

Untuk mengisi data latihan setelah akun administrator tersedia, jalankan `php artisan db:seed --class=DummyDataSeeder --force`. Seeder opsional ini menambahkan 24 kegiatan, 18 laporan dengan lampiran TXT privat dan riwayat versi, serta 12 surat perintah dengan variasi tenggat dan status. Seluruh data ditandai `DUMMY`; tanggal contoh mengikuti tanggal saat pertama kali dibuat. Menjalankan ulang melewati data contoh yang sudah ada dan memeriksa checksum berkas, tanpa menggandakan data atau mengganti akun pengguna.

### Laporan Intelijen dan jenis berkas

Menu utama **Laporan Intelijen** menyediakan tambah, detail, ubah, pencarian, filter, unduh, penghapusan sementara, dan riwayat versi berkas. Setiap catatan punya nomor laporan, tanggal, jenis, subjenis/kategori opsional, perihal, pembuat, status pemeriksaan, metadata lokasi privat, dan waktu pembuatan/perubahan.

Jenis dan subjenis dapat dikelola pada **Pengaturan Laporan**. Lapinhar, Lapinsus, Lapsus, dan Lapopsin disediakan sebagai pilihan awal nonaktif; aktifkan hanya setelah kantor mengonfirmasi pemakaiannya. DEK, DSB, dan kategori lain tidak dibuat otomatis. Kategori boleh kosong sampai konfigurasi yang sesuai praktik kantor ditambahkan. Jenis/subjenis baru divalidasi kembali di backend.

Pencarian mencakup nomor laporan, nomor referensi, perihal, jenis, dan subjenis. Filter mencakup jenis/subjenis, tanggal tertentu atau rentang tanggal, pembuat, tahun, dan status pemeriksaan. Hasil dibatasi pada pengguna yang memiliki izin melihat laporan dan dipaginasi.

Status pemeriksaan awal (`Belum diverifikasi`, `Terverifikasi`, `Perlu perbaikan`) masih pilihan administratif sementara. Konfirmasi alur/status resmi kepada kantor sebelum penggunaan nyata.

Batas upload awal 10 MiB, dapat diubah dengan `ARCHIVE_MAX_KB`. Format yang diizinkan adalah PDF, TXT, DOCX, XLSX, JPEG, dan PNG. Server memeriksa MIME dari isi berkas dan ekstensi. File yang tidak lolos pemeriksaan ditolak. Jangan memasukkan isi dokumen ke layanan search eksternal.

## Surat perintah, kalender, dan pengingat

Jenis awal adalah **Surat Perintah Tugas** dengan durasi konfigurasi 7 hari dan **Surat Perintah Opsin** dengan durasi konfigurasi 30 hari. Nilai pengembangan tersebut sementara memakai hari kalender dan tidak menghitung tanggal mulai sebagai hari pertama. Nilai ini bukan penetapan aturan resmi.

Administrator dapat mengubah dasar durasi ke hari kalender atau hari kerja dan menentukan apakah tanggal mulai dihitung sebagai hari pertama pada **Pengaturan Tenggat → Konfigurasi Jenis Surat**. Setiap surat menyimpan snapshot aturan yang dipakai saat dibuat. Perpanjangan hanya dicatat melalui aksi khusus yang meminta referensi persetujuan dan alasan; aplikasi tidak mengesahkan perpanjangan.

Pengingat memakai hari kerja Senin–Jumat dan mengabaikan Sabtu/Minggu serta tanggal aktif pada kalender hari libur aplikasi. Tidak ada hari libur bawaan yang diasumsikan berlaku. Petugas berwenang harus memelihara kalender hari libur. Aturan awal adalah 10, 7, 1, dan 0 hari kerja sebelum tenggat. Pengingat dibuat per user berizin, idempotent berdasarkan surat, aturan, penerima, dan snapshot tanggal akhir. Perubahan tanggal akhir membatalkan pengingat belum dibaca yang mengacu pada tanggal lama; riwayat tetap tersedia dan ditandai tidak berlaku.

Zona waktu aplikasi adalah `Asia/Makassar`. Pemeriksaan dapat dijalankan manual berulang kali:

```powershell
& $php artisan work-orders:generate-reminders --date=2026-10-22
& $php artisan schedule:list
& $php artisan schedule:run
```

Laravel Scheduler dijadwalkan setiap hari pukul 08.00 WITA. Untuk mengaktifkannya pada Windows, buat task melalui **Task Scheduler**:

1. Program/script: `C:\xampp62\php\php.exe`.
2. Add arguments: `C:\kejaksaan\artisan schedule:run`.
3. Start in: `C:\kejaksaan`.
4. Set trigger untuk mengulang task setiap 1 menit. Laravel sendiri akan menentukan kapan command harian dijalankan.
5. Jalankan task sekali secara manual, lalu periksa **Task Scheduler History** dan log aplikasi. Kode schedule saja tidak berarti task Windows sudah aktif.

Notifikasi hanya ditampilkan dalam aplikasi. Status baca notifikasi tidak mengubah status surat.

## Backup dan pemulihan lokal

Backup membuat satu ZIP berisi dump MySQL, berkas laporan privat, manifest SHA-256, dan mencatat hasilnya pada riwayat backup. Tujuan default adalah folder sibling di luar workspace (`C:\siak-intel-backups`); atur `BACKUP_PATH` ke media backup terpisah yang dilindungi akses dan kebijakan penyimpanan setempat. Atur `MYSQLDUMP_BINARY` jika XAMPP memakai lokasi lain.

```powershell
& $php artisan backup:create
& $php artisan backup:verify 'siak-intel-YYYYMMDD-HHMMSS.zip'
```

Backup bersifat lokal dan tidak dienkripsi oleh aplikasi. Lindungi folder tujuan dan media backup dengan ACL/enkripsi yang disetujui, batasi operator, dan uji pemulihan secara berkala. Jangan menyimpan ZIP pada folder publik.

### Prosedur pemulihan uji

1. Salin backup ke area staging terlindungi; jangan mengubah file backup asli. Jalankan `backup:verify` dan pastikan checksum lolos.
2. Ekstrak ke direktori staging, lalu buat database uji baru dengan nama yang jelas berbeda, misalnya `siak_intel_restore_test`. Jangan mengimpor ke `siak_intel_dev` atau database produksi saat latihan.
3. Impor `database.sql` hanya ke database uji tersebut memakai MySQL/MariaDB client XAMPP. Pastikan target sudah diperiksa sebelum menekan Enter.
4. Salin isi `private-archives` dari ZIP ke folder `storage/app/private/archives` pada salinan proyek staging, bukan ke folder produksi.
5. Jalankan salinan staging dengan konfigurasi koneksi database uji, lalu verifikasi record dan unduhan laporan melalui aplikasi. Jangan menguji pemulihan dengan `migrate:fresh` pada database yang menyimpan data.
6. Pemulihan layanan sebenarnya memerlukan persetujuan perubahan, backup keadaan terkini, target database/lokasi yang diperiksa dua orang, dan prosedur retensi instansi. Aplikasi belum menyediakan tombol restore otomatis.

## Pengujian

Test suite memakai SQLite in-memory dari `phpunit.xml` dan filesystem fake. Suite tidak menghapus atau mereset MySQL development.

```powershell
$env:PHPRC = 'C:\xampp62\php'
$env:PHP_INI_SCAN_DIR = ''
& 'C:\xampp62\php\php.exe' artisan test
```

Test mencakup dropdown dan relasi jenis/subjenis, jenis nonaktif, pencarian/filter/pagination, izin daftar dan unduhan, upload dua laporan dengan nama berkas sama, versi berkas, soft delete, aturan tanggal akhir, akhir pekan/libur, pergantian bulan/tahun/tahun kabisat, pengingat 10/7/1/0, idempotensi command, dan evaluasi ulang setelah perubahan tanggal.

## Sebelum penggunaan resmi

Minta persetujuan instansi tentang jenis/klasifikasi data yang boleh disimpan, konfigurasi jenis dan subjenis, lingkup akses tiap role/unit, kalender hari libur, dasar dan inklusivitas durasi, retensi, penghapusan, audit, lokasi server, backup, pemulihan, dan penanganan insiden. Konfirmasi juga apakah istilah dan proses administratif dalam aplikasi sesuai. Keberhasilan migrasi atau test tidak menyatakan kesiapan produksi.
