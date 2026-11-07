<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArchiveDownloadController;

Route::get('/', function () {
    return redirect()->route('filament.admin.pages.dashboard');
});

Route::middleware('auth')->prefix('archives')->name('archives.')->group(function (): void {
    Route::get('/{archiveDocument}/download', [ArchiveDownloadController::class, 'current'])
        ->whereNumber('archiveDocument')->name('download');
    Route::get('/{archiveDocument}/versions/{version}/download', [ArchiveDownloadController::class, 'version'])
        ->whereNumber('archiveDocument')->whereNumber('version')->name('versions.download');
});
