<?php

namespace App\Filament\Resources\WorkResource\RelationManagers;

use App\Enums\ItemStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SmsesRelationManager extends RelationManager
{
    protected static string $relationship = 'smses';

    protected static ?string $title = 'SMS xabarlar';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('phoneNumber.number')
                    ->label('Telefon raqam')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Holati')
                    ->badge()
                    ->color(fn (ItemStatus $state) => $state->color())
                    ->sortable(),

                Tables\Columns\TextColumn::make('retry')
                    ->label('Urinishlar')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Yaratilgan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Holati')
                    ->options(ItemStatus::class),
            ]);
    }
}
