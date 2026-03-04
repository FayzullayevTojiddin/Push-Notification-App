<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Models\Device;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedDevicePhoneMobile;

    protected static ?string $navigationLabel = 'Qurilmalar';

    protected static ?string $modelLabel = 'Qurilma';

    protected static ?string $pluralModelLabel = 'Qurilmalar';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Qurilma ma\'lumotlari')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nomi')
                            ->placeholder('Masalan: Samsung Galaxy A54')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone_number')
                            ->label('Telefon raqami')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('+998901234567')
                            ->maxLength(20),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Faol')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nomi')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Telefon raqami')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Faol')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_seen_at')
                    ->label('Oxirgi faollik')
                    ->since()
                    ->sortable()
                    ->placeholder('Hali ulanmagan'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Qo\'shilgan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Faol'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }
}
