<?php

namespace App\Filament\Resources\WorkResource\Pages;

use App\Filament\Resources\WorkResource;
use App\Services\WorkService;
use Filament\Resources\Pages\CreateRecord;

class CreateWork extends CreateRecord
{
    protected static string $resource = WorkResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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

    protected function afterCreate(): void
    {
        app(WorkService::class)->createWorkItems($this->record);
    }
}
