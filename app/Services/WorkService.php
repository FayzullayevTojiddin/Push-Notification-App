<?php

namespace App\Services;

use App\Enums\ItemStatus;
use App\Enums\WorkStatus;
use App\Enums\WorkType;
use App\Models\PhoneNumber;
use App\Models\Work;
use Illuminate\Support\Facades\DB;

class WorkService
{
    public function createWorkItems(Work $work): void
    {
        $phoneNumberIds = PhoneNumber::active()->pluck('id');

        if ($phoneNumberIds->isEmpty()) {
            return;
        }

        $now = now();
        $chunks = $phoneNumberIds->chunk(500);

        DB::transaction(function () use ($work, $chunks, $now) {
            foreach ($chunks as $chunk) {
                $records = $chunk->map(fn ($phoneNumberId) => [
                    'work_id' => $work->id,
                    'phone_number_id' => $phoneNumberId,
                    'status' => ItemStatus::PENDING->value,
                    'retry' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                if ($work->type === WorkType::SMS) {
                    $work->smses()->insert($records);
                } else {
                    $work->calls()->insert($records);
                }
            }
        });
    }

    public function checkAndCompleteWork(Work $work): void
    {
        $work = $work->fresh();

        if ($work->type === WorkType::SMS) {
            $hasPendingOrProcessing = $work->smses()
                ->whereIn('status', [ItemStatus::PENDING, ItemStatus::PROCESSING])
                ->exists();
        } else {
            $hasPendingOrProcessing = $work->calls()
                ->whereIn('status', [ItemStatus::PENDING, ItemStatus::PROCESSING])
                ->exists();
        }

        if (!$hasPendingOrProcessing) {
            $work->update([
                'status' => WorkStatus::COMPLETED,
                'completed_at' => now(),
            ]);
        }
    }
}
