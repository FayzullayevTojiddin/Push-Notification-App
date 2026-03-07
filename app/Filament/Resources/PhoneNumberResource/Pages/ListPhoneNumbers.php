<?php

namespace App\Filament\Resources\PhoneNumberResource\Pages;

use App\Filament\Resources\PhoneNumberResource;
use App\Imports\PhoneNumberImport;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;

class ListPhoneNumbers extends ListRecords
{
    protected static string $resource = PhoneNumberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Exceldan import')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('Excel fayl')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $import = new PhoneNumberImport();

                    Excel::import($import, storage_path('app/public/' . $data['file']));

                    Notification::make()
                        ->title('Import yakunlandi')
                        ->body("Qo'shildi: {$import->imported} ta, Takrorlangan: {$import->duplicated} ta")
                        ->success()
                        ->send();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
