<?php

namespace App\Filament\Resources\Holidays\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')->label('Tanggal')->required()->native(false)->unique(ignoreRecord: true),
                TextInput::make('name')->label('Nama hari libur')->required()->maxLength(160),
                Textarea::make('description')->label('Catatan sumber/konfirmasi')->maxLength(1000),
                Toggle::make('is_active')->label('Dikecualikan dari hari kerja')->default(true),
            ]);
    }
}
