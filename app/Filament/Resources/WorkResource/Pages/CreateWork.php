<?php

namespace App\Filament\Resources\WorkResource\Pages;

use App\Filament\Resources\WorkResource;
use App\Services\WorkService;
use Filament\Resources\Pages\CreateRecord;

class CreateWork extends CreateRecord
{
    protected static string $resource = WorkResource::class;

    protected function afterCreate(): void
    {
        app(WorkService::class)->createWorkItems($this->record);
    }
}
