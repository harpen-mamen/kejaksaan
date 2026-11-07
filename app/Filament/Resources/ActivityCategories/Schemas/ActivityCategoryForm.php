<?php

namespace App\Filament\Resources\ActivityCategories\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ActivityCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nama kategori')->required()->maxLength(120),
                TextInput::make('code')->label('Kode')->required()->maxLength(40)->alphaDash()->unique(ignoreRecord: true),
                Textarea::make('description')->label('Keterangan')->maxLength(1000),
                Toggle::make('is_active')->label('Aktif')->default(true),
            ]);
    }
}
