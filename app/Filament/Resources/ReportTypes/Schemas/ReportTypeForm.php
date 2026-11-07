<?php

namespace App\Filament\Resources\ReportTypes\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReportTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nama jenis laporan')->required()->maxLength(120),
                TextInput::make('code')->label('Kode')->required()->maxLength(40)->alphaDash()->unique(ignoreRecord: true),
                Textarea::make('description')->label('Keterangan administratif')->maxLength(1000),
                Toggle::make('is_active')->label('Aktif')->default(false)->helperText('Aktifkan setelah kantor mengonfirmasi jenis laporan ini digunakan.'),
            ]);
    }
}
