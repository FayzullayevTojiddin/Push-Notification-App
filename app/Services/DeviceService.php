<?php

namespace App\Services;

use App\Enums\ItemStatus;
use App\Enums\WorkStatus;
use App\Enums\WorkType;
use App\Models\Call;
use App\Models\Device;
use App\Models\PhoneNumber;
use App\Models\SMS;
use App\Models\Work;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DeviceService
{
    public function __construct(
        private readonly WorkService $workService,
    ) {}

    public function fetchItems(string $phoneNumber): array
    {
        $device = Device::where('phone_number', $phoneNumber)
            ->where('is_active', true)
            ->first();

        if (!$device) {
            return [];
        }

        $device->update(['last_seen_at' => now()]);

        $activeWorks = Work::active()
            ->whereIn('status', [WorkStatus::PENDING, WorkStatus::PROCESSING])
            ->oldest('id')
            ->get();

        if ($activeWorks->isEmpty()) {
            return [];
        }

        $result = [];

        foreach ($activeWorks as $work) {
            $items = $this->fetchWorkItems($work);

            if ($items->isEmpty()) {
                continue;
            }

            $this->markAsProcessing($work, $items);

            $result[] = [
                'work_id' => $work->id,
                'type' => $work->type->value,
                'message' => $work->message,
                'items' => $items->map(fn ($item) => [
                    'id' => $item->id,
                    'phone_number' => $item->phoneNumber->number,
                ])->values()->all(),
            ];

            if ($work->status === WorkStatus::PENDING) {
                $work->update([
                    'status' => WorkStatus::PROCESSING,
                    'started_at' => now(),
                ]);
            }
        }

        return $result;
    }

    private function fetchWorkItems(Work $work): Collection
    {
        if ($work->type === WorkType::SMS) {
            return SMS::where('work_id', $work->id)
                ->where('status', ItemStatus::PENDING)
                ->with('phoneNumber:id,number')
                ->oldest('id')
                ->limit(30)
                ->get();
        }

        return Call::where('work_id', $work->id)
            ->where('status', ItemStatus::PENDING)
            ->with('phoneNumber:id,number')
            ->oldest('id')
            ->limit(1)
            ->get();
    }

    private function markAsProcessing(Work $work, Collection $items): void
    {
        $ids = $items->pluck('id');

        if ($work->type === WorkType::SMS) {
            SMS::whereIn('id', $ids)->update(['status' => ItemStatus::PROCESSING]);
        } else {
            Call::whereIn('id', $ids)->update(['status' => ItemStatus::PROCESSING]);
        }
    }

    public function reportItems(string $phoneNumber, array $items): void
    {
        $device = Device::where('phone_number', $phoneNumber)
            ->where('is_active', true)
            ->firstOrFail();

        $device->update(['last_seen_at' => now()]);

        $itemIds = collect($items)->pluck('id');

        $smsItems = SMS::whereIn('id', $itemIds)
            ->where('status', ItemStatus::PROCESSING)
            ->get()
            ->keyBy('id');

        $callItems = Call::whereIn('id', $itemIds)
            ->where('status', ItemStatus::PROCESSING)
            ->get()
            ->keyBy('id');

        $affectedWorkIds = collect();
        $phoneNumberUpdates = collect();

        DB::transaction(function () use ($items, $smsItems, $callItems, &$affectedWorkIds, &$phoneNumberUpdates) {
            foreach ($items as $item) {
                $id = $item['id'];
                $status = ItemStatus::from($item['status']);
                $response = $item['response'] ?? null;

                $record = $smsItems->get($id) ?? $callItems->get($id);

                if (!$record) {
                    continue;
                }

                $updateData = [
                    'status' => $status,
                    'response' => $response,
                ];

                if ($status === ItemStatus::FAILED) {
                    $updateData['retry'] = $record->retry + 1;
                }

                $record->update($updateData);
                $affectedWorkIds->push($record->work_id);
                $phoneNumberUpdates->push([
                    'phone_number_id' => $record->phone_number_id,
                    'status' => $status,
                ]);
            }
        });

        $phoneNumberUpdates->groupBy('phone_number_id')->each(function ($updates, $phoneNumberId) {
            $phoneNumber = PhoneNumber::find($phoneNumberId);

            if (!$phoneNumber) {
                return;
            }

            $hasSent = $updates->contains('status', ItemStatus::SENT);
            $allFailed = $updates->every(fn ($u) => $u['status'] === ItemStatus::FAILED);

            if ($hasSent) {
                $phoneNumber->resetFailed();
            } elseif ($allFailed) {
                $phoneNumber->incrementFailed();
            }
        });

        $affectedWorkIds->unique()->each(function ($workId) {
            $work = Work::find($workId);
            if ($work) {
                $this->workService->checkAndCompleteWork($work);
            }
        });
    }
}
