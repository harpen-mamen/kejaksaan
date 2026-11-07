<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view' => ['Lihat dashboard', 'Melihat ringkasan yang diizinkan.'],
            'categories.view' => ['Lihat kategori', 'Melihat kategori kegiatan generik.'],
            'categories.manage' => ['Kelola kategori', 'Membuat dan memperbarui kategori.'],
            'activities.view' => ['Lihat kegiatan', 'Melihat data kegiatan yang diizinkan.'],
            'activities.create' => ['Buat kegiatan', 'Membuat data kegiatan administratif.'],
            'activities.update' => ['Ubah kegiatan', 'Memperbarui data kegiatan.'],
            'activities.delete' => ['Hapus sementara kegiatan', 'Menghapus sementara data kegiatan dan mempertahankan riwayatnya.'],
            'archives.view' => ['Lihat laporan', 'Melihat data laporan sesuai izin.'],
            'archives.upload' => ['Tambah laporan', 'Menambahkan catatan laporan baru.'],
            'archives.update' => ['Ubah laporan', 'Memperbarui data laporan atau menambahkan versi berkas.'],
            'archives.download' => ['Unduh berkas laporan', 'Mengunduh berkas laporan setelah pemeriksaan izin.'],
            'archives.delete' => ['Hapus sementara laporan', 'Menghapus sementara catatan laporan tanpa menghapus berkasnya.'],
            'archives.verify' => ['Periksa laporan', 'Mengubah status pemeriksaan laporan sesuai alur yang disetujui.'],
            'users.view' => ['Lihat pengguna', 'Melihat akun pengguna.'],
            'users.manage' => ['Kelola pengguna', 'Mengelola akun dan status akses.'],
            'roles.manage' => ['Kelola peran', 'Mengelola peran dan izinnya.'],
            'audit.view' => ['Lihat audit log', 'Melihat log aktivitas tanpa mengubahnya.'],
            'backup.manage' => ['Kelola pencadangan', 'Membuat dan memeriksa cadangan lokal.'],
            'settings.manage' => ['Kelola pengaturan', 'Mengelola konfigurasi administratif.'],
            'work_orders.view' => ['Lihat surat perintah', 'Melihat surat perintah dan pengingat yang diizinkan.'],
            'work_orders.manage' => ['Kelola surat perintah', 'Membuat dan memperbarui surat perintah.'],
            'work_orders.extend' => ['Perpanjang surat perintah', 'Mencatat perpanjangan dengan referensi persetujuan.'],
            'work_orders.close' => ['Tutup surat perintah', 'Mencatat penutupan administratif surat perintah.'],
            'holidays.manage' => ['Kelola kalender libur', 'Memelihara tanggal libur resmi yang dikonfirmasi.'],
            'reminder_rules.manage' => ['Kelola aturan pengingat', 'Mengubah ambang pengingat hari kerja.'],
        ];

        $permissionIds = [];
        foreach ($permissions as $name => [$label, $description]) {
            $permissionIds[$name] = Permission::firstOrCreate(
                ['name' => $name],
                ['label' => $label, 'description' => $description],
            )->id;
        }

        $rolePermissions = [
            'administrator-sistem' => array_keys($permissions),
            'petugas-arsip' => [
                'dashboard.view', 'categories.view', 'categories.manage',
                'activities.view', 'activities.create', 'activities.update',
                'archives.view', 'archives.upload', 'archives.update', 'archives.download', 'archives.verify',
                'work_orders.view', 'work_orders.manage', 'work_orders.close',
            ],
            'pengguna-baca' => [
                'dashboard.view', 'categories.view', 'activities.view', 'archives.view', 'archives.download', 'work_orders.view',
            ],
        ];

        $roles = [
            'administrator-sistem' => ['Administrator Sistem', 'Kelola konfigurasi dan akun sesuai kewenangan yang disetujui.'],
            'petugas-arsip' => ['Petugas Laporan', 'Kelola data laporan dan kegiatan sesuai lingkup tugas.'],
            'pengguna-baca' => ['Pengguna Baca', 'Lihat atau unduh laporan yang memang diizinkan.'],
        ];

        foreach ($roles as $slug => [$name, $description]) {
            $role = Role::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description, 'is_system' => true],
            );

            $role->permissions()->sync(collect($rolePermissions[$slug])->map(fn (string $permission) => $permissionIds[$permission]));
        }
    }
}
