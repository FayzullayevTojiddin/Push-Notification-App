<?php

namespace App\Console\Commands;

use App\Enums\ItemStatus;
use App\Models\Call;
use App\Models\SMS;
use Illuminate\Console\Command;

class ResetStuckItems extends Command
{
    protected $signature = 'app:reset-stuck-items {--minutes=10 : PROCESSING da qolib ketgan vaqt (daqiqa)}';

    protected $description = 'PROCESSING da qolib ketgan itemlarni PENDING ga qaytaradi';

    public function handle(): void
    {
        $minutes = (int) $this->option('minutes');
        $threshold = now()->subMinutes($minutes);

        $smsCount = SMS::where('status', ItemStatus::PROCESSING)
            ->where('updated_at', '<', $threshold)
            ->update(['status' => ItemStatus::PENDING]);

        $callCount = Call::where('status', ItemStatus::PROCESSING)
            ->where('updated_at', '<', $threshold)
            ->update(['status' => ItemStatus::PENDING]);

        $this->info("{$smsCount} ta SMS va {$callCount} ta qo'ng'iroq PENDING ga qaytarildi.");
    }
}
