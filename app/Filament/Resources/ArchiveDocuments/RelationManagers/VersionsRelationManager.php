<?php

namespace App\Filament\Resources\ArchiveDocuments\RelationManagers;

use App\Models\ArchiveDocumentVersion;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';
    protected static ?string $title = 'Riwayat Versi Berkas';

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('version_number')->label('Versi')->sortable(),
            TextColumn::make('original_file_name')->label('Nama berkas asli')->limit(60),
            TextColumn::make('file_size')->label('Ukuran')->numeric(),
            TextColumn::make('mime_type')->label('MIME'),
            TextColumn::make('sha256')->label('SHA-256')->copyable()->limit(24),
            TextColumn::make('uploader.name')->label('Pengunggah'),
            TextColumn::make('uploaded_at')->label('Waktu unggah')->dateTime()->sortable(),
        ])->recordActions([
            Action::make('download')->label('Unduh versi')->url(fn (ArchiveDocumentVersion $record) => route('archives.versions.download', ['archiveDocument' => $this->getOwnerRecord()->getKey(), 'version' => $record->getKey()]))->openUrlInNewTab(),
        ]);
    }
}
