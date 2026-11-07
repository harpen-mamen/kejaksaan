<?php
namespace App\Filament\Resources\ReminderRules;
use App\Models\ReminderRule;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
class ReminderRuleResource extends Resource
{
    protected static ?string $model = ReminderRule::class;
    protected static ?string $modelLabel = 'aturan pengingat';
    protected static ?string $pluralModelLabel = 'Aturan Pengingat';
    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan Tenggat';
    public static function canViewAny(): bool { return auth()->user()?->hasPermissionTo('reminder_rules.manage') ?? false; }
    public static function canCreate(): bool { return static::canViewAny(); }
    public static function canEdit($record): bool { return static::canViewAny(); }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }
    public static function form(Schema $schema): Schema { return $schema->components([
        TextInput::make('name')->label('Nama pengingat')->required()->maxLength(100),
        TextInput::make('working_days_before')->label('Hari kerja sebelum tenggat (0 = tenggat/berakhir)')->numeric()->minValue(0)->maxValue(365)->required()->unique(ignoreRecord: true),
        Toggle::make('is_active')->label('Aktif')->default(true),
    ]); }
    public static function table(Table $table): Table { return $table->columns([
        TextColumn::make('name')->label('Aturan')->searchable(), TextColumn::make('working_days_before')->label('Hari kerja sebelum tenggat')->sortable(),
        TextColumn::make('is_active')->label('Aktif')->badge()->formatStateUsing(fn ($state) => $state ? 'Ya' : 'Tidak'),
    ])->recordActions([\Filament\Actions\EditAction::make()]); }
    public static function getPages(): array { return ['index' => Pages\ListReminderRules::route('/'), 'create' => Pages\CreateReminderRule::route('/create'), 'edit' => Pages\EditReminderRule::route('/{record}/edit')]; }
}
