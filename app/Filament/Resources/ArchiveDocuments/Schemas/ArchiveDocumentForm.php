<?php

namespace App\Filament\Resources\ArchiveDocuments\Schemas;

use App\Models\Activity;
use App\Models\ArchiveDocument;
use App\Models\ReportSubtype;
use App\Models\ReportType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;
use Filament\Schemas\Components\Utilities\Set;

class ArchiveDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('archive_code')->label('Nomor laporan')->required()->maxLength(80)->unique(ignoreRecord: true),
                Select::make('report_type_id')->label('Jenis laporan')->options(fn () => ReportType::where('is_active', true)->orderBy('name')->pluck('name', 'id'))->searchable()->live()->afterStateUpdated(fn (Set $set) => $set('report_subtype_id', null))->required(),
                Select::make('report_subtype_id')->label('Subjenis/kategori (opsional)')->options(fn ($get) => ReportSubtype::activeOptionsForType($get('report_type_id') ? (int) $get('report_type_id') : null)->all())->searchable()->nullable()->rules(fn ($get) => [Rule::exists('report_subtypes', 'id')->where('report_type_id', $get('report_type_id'))->where('is_active', true)]),
                Select::make('activity_id')->label('Kegiatan terkait')->options(fn () => Activity::query()->orderBy('title')->pluck('title', 'id'))->searchable()->nullable(),
                TextInput::make('title')->label('Perihal singkat')->required()->maxLength(200),
                TextInput::make('document_number')->label('Nomor referensi terkait')->maxLength(100),
                DatePicker::make('report_date')->label('Tanggal laporan')->required()->native(false),
                FileUpload::make('upload')->label('Berkas')->disk('archive_private')->directory('pending')->visibility('private')->storeFiles(false)->acceptedFileTypes(config('archives.mime_types'))->maxSize((int) config('archives.max_kb'))->required(fn (string $operation) => $operation === 'create')->helperText('File privat; nama penyimpanan dibuat acak. Berkas lama dipertahankan sebagai versi.'),
                Select::make('sending_status')->label('Status pengiriman')->options(['belum_dikirim' => 'Belum dikirim', 'terkirim' => 'Terkirim'])->default('belum_dikirim')->required(),
                DatePicker::make('sent_to_kejati_at')->label('Tanggal pengiriman')->native(false)->required(fn ($get) => $get('sending_status') === 'terkirim'),
                Select::make('verification_status')->label('Status pemeriksaan')->options(ArchiveDocument::VERIFICATION_STATUSES)->default('belum_diverifikasi')->required()->helperText('Pilihan status ini bersifat sementara sampai alur pemeriksaan kantor dikonfirmasi.'),
                Textarea::make('administrative_notes')->label('Catatan administratif')->maxLength(2000)->columnSpanFull(),
            ]);
    }
}
