<?php

namespace App\Filament\Resources\ArchiveDocuments;

use App\Filament\Resources\ArchiveDocuments\Pages\CreateArchiveDocument;
use App\Filament\Resources\ArchiveDocuments\Pages\EditArchiveDocument;
use App\Filament\Resources\ArchiveDocuments\Pages\ListArchiveDocuments;
use App\Filament\Resources\ArchiveDocuments\Pages\ViewArchiveDocument;
use App\Filament\Resources\ArchiveDocuments\Schemas\ArchiveDocumentForm;
use App\Filament\Resources\ArchiveDocuments\Tables\ArchiveDocumentsTable;
use App\Models\ArchiveDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ArchiveDocumentResource extends Resource
{
    protected static ?string $modelLabel = 'laporan';
    protected static ?string $pluralModelLabel = 'Laporan Intelijen';
    protected static ?string $navigationLabel = 'Laporan Intelijen';
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan Intelijen';
    protected static ?string $model = ArchiveDocument::class;

    public static function canViewAny(): bool { return auth()->user()?->hasPermissionTo('archives.view') ?? false; }
    public static function canCreate(): bool { return auth()->user()?->hasPermissionTo('archives.upload') ?? false; }
    public static function canEdit($record): bool { return auth()->user()?->hasPermissionTo('archives.update') ?? false; }
    public static function canDelete($record): bool { return auth()->user()?->hasPermissionTo('archives.delete') ?? false; }
    public static function canDeleteAny(): bool { return false; }
    public static function canForceDelete($record): bool { return false; }
    public static function canRestore($record): bool { return auth()->user()?->hasPermissionTo('archives.delete') ?? false; }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ArchiveDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArchiveDocumentsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('archive_code')->label('Nomor laporan'),
            TextEntry::make('document_number')->label('Nomor referensi terkait')->placeholder('—'),
            TextEntry::make('report_date')->label('Tanggal laporan')->date(),
            TextEntry::make('reportType.name')->label('Jenis laporan'),
            TextEntry::make('reportSubtype.name')->label('Subjenis/kategori')->placeholder('Belum ditetapkan'),
            TextEntry::make('title')->label('Perihal singkat'),
            TextEntry::make('createdBy.name')->label('Pembuat')->placeholder('Tidak diketahui'),
            TextEntry::make('verification_status')->label('Status pemeriksaan')->formatStateUsing(fn ($state) => ArchiveDocument::VERIFICATION_STATUSES[$state] ?? $state),
            TextEntry::make('original_file_name')->label('Berkas laporan'),
            TextEntry::make('created_at')->label('Dibuat')->dateTime(),
            TextEntry::make('updated_at')->label('Diperbarui')->dateTime(),
            TextEntry::make('administrative_notes')->label('Catatan administratif')->placeholder('—')->columnSpanFull(),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\ArchiveDocuments\RelationManagers\VersionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArchiveDocuments::route('/'),
            'create' => CreateArchiveDocument::route('/create'),
            'view' => ViewArchiveDocument::route('/{record}'),
            'edit' => EditArchiveDocument::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
