# Audit keamanan dan kelengkapan SIAK Intel

Tanggal pemeriksaan: 7 November 2026. Pemeriksaan ini adalah tinjauan statis atas route, autentikasi panel, resource Filament, policy, layanan domain, middleware, seeder akses, migrasi, test yang tersedia, dan dokumentasi. Ini bukan uji penetrasi atau persetujuan untuk data resmi.

## Perbaikan dalam pemeriksaan ini

- Seeder akses kini menyamakan seluruh izin role bawaan dengan daftar yang didefinisikan. Sebelumnya, menjalankan ulang seeder hanya menambah izin sehingga izin yang dicabut dari konfigurasi bisa tetap aktif di basis data.
- Perubahan role hanya dapat dilakukan pemegang `roles.manage`; `users.manage` saja tidak lagi dapat menaikkan hak akses akun. Form juga mencegah admin menghapus role sendiri atau menonaktifkan akunnya melalui layar yang sama.
- Aksi perpanjangan dan penutupan surat perintah kini tersedia pada halaman detail sesuai izin masing-masing, tanpa mensyaratkan `work_orders.manage`. Layanan domain memeriksa policy sebelum mengubah data.
- Aksi tandai pengingat dibaca memeriksa ulang izin dan kepemilikan pengingat di server.
- Audit perubahan akun dan role sekarang mencatat daftar role/izin sebelum dan sesudah perubahan.
- Gambar asli `image.png` dipasang sebagai logo panel dan favicon.
- Profil mandiri diaktifkan agar pengguna dapat memperbarui kata sandi setelah verifikasi kata sandi saat ini.
- MFA TOTP opsional dengan recovery code tersedia dari profil; secret dan recovery code disimpan terenkripsi menggunakan `APP_KEY`.

## Temuan yang masih perlu keputusan

### Cakupan data

Pengguna mengonfirmasi izin lihat berlaku untuk semua data. Query `activities.view`, `archives.view`, dan `work_orders.view` saat ini memang memberi akses seluruh catatan kepada pemegang izin tersebut. Bila kebijakan berubah, pembatasan unit memerlukan desain relasi organisasi dan pengujian akses lintas unit.

### Tinggi: pengelola role memiliki kewenangan penuh atas izin

Pemegang `roles.manage` dapat memberikan izin apa pun ke role, termasuk `users.manage`, `backup.manage`, dan `roles.manage`. Untuk mengganti role akun, pengelola juga perlu `users.manage` agar dapat membuka form pengguna. Role administrator bawaan memang memiliki seluruh izin. Tetapkan siapa yang boleh memegang izin tersebut, proses persetujuan perubahan hak, dan apakah perubahan perlu persetujuan kedua.

### Sedang: reset kata sandi jarak jauh dan MFA wajib belum tersedia

Profil mandiri mendukung penggantian kata sandi setelah verifikasi kata sandi saat ini dan pendaftaran MFA TOTP opsional dengan recovery code. Reset melalui email belum tersedia, dan kebijakan belum mewajibkan MFA untuk seluruh akun atau akun administrator. Prosedur pemulihan admin jika semua akun admin tidak dapat masuk perlu disepakati.

### Sedang: retensi dan penghapusan fisik belum ditetapkan

Penghapusan laporan bersifat soft delete dan berkas beserta versinya tetap tersimpan. Belum ada jadwal retensi atau proses pemusnahan berkas kedaluwarsa. Tentukan lama penyimpanan, otorisasi pemusnahan, kebutuhan legal hold, dan pencatatan bukti penghapusan.

### Sedang: backup lokal tidak dienkripsi oleh aplikasi

Backup memuat dump basis data dan seluruh berkas laporan. Aplikasi membuat checksum, tetapi tidak mengenkripsi ZIP; proteksi saat disimpan bergantung pada ACL atau enkripsi media di luar aplikasi. Kebijakan retensi backup dan uji pemulihan berkala juga perlu ditetapkan.

### Keputusan proses yang belum terkonfirmasi

README sudah menandai jenis laporan, status pemeriksaan, aturan durasi surat, kalender libur, zona waktu, retensi, serta lingkup role sebagai nilai pengembangan atau keputusan kantor yang perlu dikonfirmasi. Seeder tidak mengisi hari libur resmi dan tidak membuat akun pengguna otomatis. Scheduler Windows juga perlu diaktifkan secara operasional; kode jadwal saja tidak menjalankan pengingat.

## Kontrol yang ditemukan

- Panel Filament mewajibkan login dan akun aktif dengan `dashboard.view`; akun nonaktif gagal memperoleh izin melalui `hasPermissionTo`.
- Policy memeriksa izin untuk fitur utama; unduhan laporan melewati policy dan membaca berkas dari private disk, dengan pemeriksaan path dan header `nosniff`.
- Penghapusan permanen laporan dan surat perintah dinonaktifkan; unduhan serta perubahan penting dicatat ke audit log.
- Pengingat dibatasi pada pemiliknya. Scheduler menghasilkan pengingat bagi akun aktif yang memiliki `work_orders.view`.
- Login gagal, login berhasil, logout, request ID, dan kegagalan otorisasi memiliki pencatatan.

## Batas pemeriksaan

Seluruh suite aplikasi lulus: 20 test dan 124 assertion. Sintaks PHP pada berkas yang diubah diperiksa dengan `php -l`; koneksi XAMPP dan status migrasi diperiksa. Satu migrasi tambahan MFA berhasil diterapkan setelah semua migrasi sebelumnya dipastikan selesai. Pemindaian dependensi, pemeriksaan konfigurasi server, dan uji browser tetap perlu dilakukan sebelum deployment resmi.
