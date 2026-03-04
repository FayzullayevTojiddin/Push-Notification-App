<?php

namespace App\Filament\Resources;

use App\Enums\WorkStatus;
use App\Enums\WorkType;
use App\Filament\Resources\WorkResource\Pages;
use App\Filament\Resources\WorkResource\RelationManagers;
use App\Models\Work;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class WorkResource extends Resource
{
    protected static ?string $model = Work::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $navigationLabel = 'Ishlar';

    protected static ?string $modelLabel = 'Ish';

    protected static ?string $pluralModelLabel = 'Ishlar';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ish tafsilotlari')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Sarlavha')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->label('Turi')
                            ->options(WorkType::class)
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('status')
                            ->label('Holati')
                            ->options(WorkStatus::class)
                            ->default(WorkStatus::PENDING)
                            ->required()
                            ->native(false),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Faol')
                            ->default(true),

                        Forms\Components\KeyValue::make('message')
                            ->label('Xabar ma\'lumotlari')
                            ->reorderable(),

                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Rejalashtirilgan vaqt'),
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

                Tables\Columns\TextColumn::make('title')
                    ->label('Sarlavha')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Turi')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Holati')
                    ->badge()
                    ->color(fn (WorkStatus $state) => $state->color())
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Faol'),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Jarayon')
                    ->suffix('%')
                    ->getStateUsing(fn (Work $record) => $record->progress),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Rejalashtirilgan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Boshlangan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Tugallangan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Yaratilgan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Turi')
                    ->options(WorkType::class),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Holati')
                    ->options(WorkStatus::class),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Faol'),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CallsRelationManager::class,
            RelationManagers\SmsesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorks::route('/'),
            'create' => Pages\CreateWork::route('/create'),
            'view' => Pages\ViewWork::route('/{record}'),
            'edit' => Pages\EditWork::route('/{record}/edit'),
        ];
    }
}
