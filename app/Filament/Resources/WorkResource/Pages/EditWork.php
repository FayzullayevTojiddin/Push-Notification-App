<?php

namespace App\Filament\Resources\WorkResource\Pages;

use App\Enums\WorkType;
use App\Filament\Resources\WorkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWork extends EditRecord
{
    protected static string $resource = WorkResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $message = $data['message'] ?? [];

        $data['sms_message'] = $message['text'] ?? '';
        $data['call_audio'] = $message['audio_url'] ?? '';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $type = $data['type'] instanceof WorkType ? $data['type']->value : $data['type'];
        $message = [];

        if ($type === 'sms') {
            $message['text'] = $data['sms_message'] ?? '';
        } elseif ($type === 'call') {
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
