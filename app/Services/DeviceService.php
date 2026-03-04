<?php

namespace App\Services;

use App\Enums\ItemStatus;
use App\Enums\WorkStatus;
use App\Enums\WorkType;
use App\Models\Call;
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
        $phone = PhoneNumber::where('number', $phoneNumber)
            ->where('is_active', true)
            ->first();

        if (!$phone) {
            return [];
        }

        $activeWorks = Work::active()
            ->whereIn('status', [WorkStatus::PENDING, WorkStatus::PROCESSING])
            ->oldest('id')
            ->get();

        if ($activeWorks->isEmpty()) {
            return [];
        }

        $result = [];

        foreach ($activeWorks as $work) {
            $items = $this->fetchWorkItems($work, $phone);

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

    private function fetchWorkItems(Work $work, PhoneNumber $phone): Collection
    {
        if ($work->type === WorkType::SMS) {
            return SMS::where('work_id', $work->id)
                ->where('phone_number_id', $phone->id)
                ->where('status', ItemStatus::PENDING)
                ->with('phoneNumber:id,number')
                ->oldest('id')
                ->limit(30)
                ->get();
        }

        return Call::where('work_id', $work->id)
            ->where('phone_number_id', $phone->id)
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
        $phone = PhoneNumber::where('number', $phoneNumber)
            ->where('is_active', true)
            ->firstOrFail();

        $itemIds = collect($items)->pluck('id');

        $smsItems = SMS::whereIn('id', $itemIds)
            ->where('phone_number_id', $phone->id)
            ->get()
            ->keyBy('id');

        $callItems = Call::whereIn('id', $itemIds)
            ->where('phone_number_id', $phone->id)
            ->get()
            ->keyBy('id');

        $affectedWorkIds = collect();

        DB::transaction(function () use ($items, $smsItems, $callItems, &$affectedWorkIds) {
            foreach ($items as $item) {
                $id = $item['id'];
                $status = ItemStatus::from($item['status']);
                $response = $item['response'] ?? null;

                if ($smsItems->has($id)) {
                    $sms = $smsItems->get($id);
                    $updateData = [
                        'status' => $status,
                        'response' => $response,
                    ];
                    if ($status === ItemStatus::FAILED) {
                        $updateData['retry'] = $sms->retry + 1;
                    }
                    $sms->update($updateData);
                    $affectedWorkIds->push($sms->work_id);
                } elseif ($callItems->has($id)) {
                    $call = $callItems->get($id);
                    $updateData = [
                        'status' => $status,
                        'response' => $response,
                    ];
                    if ($status === ItemStatus::FAILED) {
                        $updateData['retry'] = $call->retry + 1;
                    }
                    $call->update($updateData);
                    $affectedWorkIds->push($call->work_id);
                }
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
