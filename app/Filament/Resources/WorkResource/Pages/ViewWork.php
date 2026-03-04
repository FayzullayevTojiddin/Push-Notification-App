<?php

namespace App\Filament\Resources\WorkResource\Pages;

use App\Enums\ItemStatus;
use App\Enums\WorkType;
use App\Filament\Resources\WorkResource;
use App\Models\Work;
use Filament\Actions;
use Filament\Infolists;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewWork extends ViewRecord
{
    protected static string $resource = WorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Ish tafsilotlari')
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label('Sarlavha'),
                        Infolists\Components\TextEntry::make('type')
                            ->label('Turi')
                            ->badge(),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Holati')
                            ->badge()
                            ->color(fn ($state) => $state->color()),
                        Infolists\Components\IconEntry::make('is_active')
                            ->boolean()
                            ->label('Faol'),
                        Infolists\Components\TextEntry::make('message')
                            ->label('Xabar')
                            ->getStateUsing(fn (Work $record) => json_encode($record->message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
                        Infolists\Components\TextEntry::make('scheduled_at')
                            ->label('Rejalashtirilgan')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('started_at')
                            ->label('Boshlangan')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Tugallangan')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Jarayon')
                    ->schema([
                        Infolists\Components\TextEntry::make('progress_display')
                            ->label('Bajarilish')
                            ->getStateUsing(function (Work $record) {
                                return $record->progress . '%';
                            }),
                        Infolists\Components\TextEntry::make('total_items')
                            ->label('Jami')
                            ->getStateUsing(function (Work $record) {
                                if ($record->type === WorkType::SMS) {
                                    return $record->smses()->count();
                                }
                                return $record->calls()->count();
                            }),
                        Infolists\Components\TextEntry::make('sent_items')
                            ->label('Yuborilgan')
                            ->getStateUsing(function (Work $record) {
                                if ($record->type === WorkType::SMS) {
                                    return $record->smses()->where('status', ItemStatus::SENT)->count();
                                }
                                return $record->calls()->where('status', ItemStatus::SENT)->count();
                            }),
                        Infolists\Components\TextEntry::make('failed_items')
                            ->label('Xatolik')
                            ->getStateUsing(function (Work $record) {
                                if ($record->type === WorkType::SMS) {
                                    return $record->smses()->where('status', ItemStatus::FAILED)->count();
                                }
                                return $record->calls()->where('status', ItemStatus::FAILED)->count();
                            }),
                        Infolists\Components\TextEntry::make('pending_items')
                            ->label('Kutilmoqda')
                            ->getStateUsing(function (Work $record) {
                                if ($record->type === WorkType::SMS) {
                                    return $record->smses()->where('status', ItemStatus::PENDING)->count();
                                }
                                return $record->calls()->where('status', ItemStatus::PENDING)->count();
                            }),
                        Infolists\Components\TextEntry::make('processing_items')
                            ->label('Jarayonda')
                            ->getStateUsing(function (Work $record) {
                                if ($record->type === WorkType::SMS) {
                                    return $record->smses()->where('status', ItemStatus::PROCESSING)->count();
                                }
                                return $record->calls()->where('status', ItemStatus::PROCESSING)->count();
                            }),
                    ])
                    ->columns(3),
            ]);
    }
}
