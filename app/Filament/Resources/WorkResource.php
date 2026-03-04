<?php

namespace App\Filament\Resources;

use App\Enums\WorkStatus;
use App\Enums\WorkType;
use App\Filament\Resources\WorkResource\Pages\CreateWork;
use App\Filament\Resources\WorkResource\Pages\EditWork;
use App\Filament\Resources\WorkResource\Pages\ListWorks;
use App\Filament\Resources\WorkResource\Pages\ViewWork;
use App\Filament\Resources\WorkResource\RelationManagers\CallsRelationManager;
use App\Filament\Resources\WorkResource\RelationManagers\SmsesRelationManager;
use App\Models\Work;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
                Section::make('Ish tafsilotlari')
                    ->schema([
                        TextInput::make('title')
                            ->label('Sarlavha')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->label('Turi')
                            ->options(WorkType::class)
                            ->required()
                            ->native(false),

                        Select::make('status')
                            ->label('Holati')
                            ->options(WorkStatus::class)
                            ->default(WorkStatus::PENDING)
                            ->required()
                            ->native(false),

                        Toggle::make('is_active')
                            ->label('Faol')
                            ->default(true),

                        KeyValue::make('message')
                            ->label('Xabar ma\'lumotlari')
                            ->reorderable(),

                        DateTimePicker::make('scheduled_at')
                            ->label('Rejalashtirilgan vaqt'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Sarlavha')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Turi')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Holati')
                    ->badge()
                    ->color(fn (WorkStatus $state) => $state->color())
                    ->sortable(),

                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Faol'),

                TextColumn::make('progress')
                    ->label('Jarayon')
                    ->suffix('%')
                    ->getStateUsing(fn (Work $record) => $record->progress),

                TextColumn::make('scheduled_at')
                    ->label('Rejalashtirilgan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('started_at')
                    ->label('Boshlangan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('completed_at')
                    ->label('Tugallangan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Yaratilgan')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Turi')
                    ->options(WorkType::class),

                SelectFilter::make('status')
                    ->label('Holati')
                    ->options(WorkStatus::class),

                TernaryFilter::make('is_active')
                    ->label('Faol'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CallsRelationManager::class,
            SmsesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorks::route('/'),
            'create' => CreateWork::route('/create'),
            'view' => ViewWork::route('/{record}'),
            'edit' => EditWork::route('/{record}/edit'),
        ];
    }
}
