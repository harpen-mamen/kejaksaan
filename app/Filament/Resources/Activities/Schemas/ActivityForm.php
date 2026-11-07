<?php

namespace App\Filament\Resources\Activities\Schemas;

use App\Models\ActivityCategory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('activity_category_id')->label('Kategori')->options(fn () => ActivityCategory::where('is_active', true)->orderBy('name')->pluck('name', 'id'))->searchable()->required(),
                TextInput::make('reference_code')->label('Kode referensi')->required()->maxLength(80)->unique(ignoreRecord: true),
                TextInput::make('title')->label('Nama kegiatan')->required()->maxLength(200),
                DatePicker::make('activity_date')->label('Tanggal kegiatan')->required()->native(false),
                TextInput::make('unit')->label('Unit administratif')->maxLength(120),
                Select::make('report_status')->label('Status laporan')->options(['belum_dikirim' => 'Belum dikirim', 'dikirim' => 'Dikirim', 'perlu_perbaikan' => 'Perlu perbaikan'])->default('belum_dikirim')->required(),
                DatePicker::make('report_submitted_at')->label('Tanggal pengiriman laporan')->native(false),
                Textarea::make('description')->label('Keterangan administratif')->maxLength(2000)->columnSpanFull(),
            ]);
    }
}
