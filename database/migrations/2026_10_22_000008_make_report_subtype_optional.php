<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('archive_documents', function (Blueprint $table): void {
            $table->foreignId('report_subtype_id')->nullable()->change();
        });

        DB::table('permissions')->where('name', 'activities.delete')->update([
            'label' => 'Hapus sementara kegiatan',
            'description' => 'Menghapus sementara data kegiatan dan mempertahankan riwayatnya.',
        ]);

        DB::table('permissions')->where('name', 'archives.view')->update([
            'label' => 'Lihat laporan',
            'description' => 'Melihat data laporan sesuai izin.',
        ]);
        DB::table('permissions')->where('name', 'archives.upload')->update([
            'label' => 'Tambah laporan',
            'description' => 'Menambahkan catatan laporan baru.',
        ]);
        DB::table('permissions')->where('name', 'archives.update')->update([
            'label' => 'Ubah laporan',
            'description' => 'Memperbarui data laporan atau menambahkan versi berkas.',
        ]);
        DB::table('permissions')->where('name', 'archives.download')->update([
            'label' => 'Unduh berkas laporan',
            'description' => 'Mengunduh berkas laporan setelah pemeriksaan izin.',
        ]);
        DB::table('permissions')->where('name', 'archives.delete')->update([
            'label' => 'Hapus sementara laporan',
            'description' => 'Menghapus sementara catatan laporan tanpa menghapus berkasnya.',
        ]);
        DB::table('permissions')->where('name', 'archives.verify')->update([
            'label' => 'Periksa laporan',
            'description' => 'Mengubah status pemeriksaan laporan sesuai alur yang disetujui.',
        ]);

        DB::table('roles')->where('slug', 'petugas-arsip')->where('name', 'Petugas Arsip')->update([
            'name' => 'Petugas Laporan',
            'description' => 'Kelola data laporan dan kegiatan sesuai lingkup tugas.',
        ]);
        DB::table('roles')->where('slug', 'pengguna-baca')->where('name', 'Pengguna Baca')->update([
            'description' => 'Lihat atau unduh laporan yang memang diizinkan.',
        ]);
    }

    public function down(): void
    {
        if (DB::table('archive_documents')->whereNull('report_subtype_id')->exists()) {
            throw new RuntimeException('Cannot require report subtypes while reports without a subtype exist.');
        }

        Schema::table('archive_documents', function (Blueprint $table): void {
            $table->foreignId('report_subtype_id')->nullable(false)->change();
        });
    }
};
