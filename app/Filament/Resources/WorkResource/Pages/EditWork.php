<?php

namespace App\Filament\Resources\WorkResource\Pages;

use App\Filament\Resources\WorkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWork extends EditRecord
{
    protected static string $resource = WorkResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $message = $data['message'] ?? [];

        $data['sms_message'] = $message['message'] ?? '';
        $data['call_audio'] = $message['audio_url'] ?? '';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $message = [];

        if ($data['type'] === 'sms') {
            $message['message'] = $data['sms_message'] ?? '';
        } elseif ($data['type'] === 'call') {
            $message['audio_url'] = $data['call_audio'] ?? '';
        }

        $data['message'] = $message;
        unset($data['sms_message'], $data['call_audio']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
