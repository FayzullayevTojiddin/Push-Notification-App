<?php

namespace App\Filament\Widgets;

use App\Enums\ItemStatus;
use App\Models\Call;
use App\Models\SMS;
use App\Models\Work;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalWorks = Work::count();
        $totalSms = SMS::count();
        $totalCalls = Call::count();

        $sentSms = SMS::where('status', ItemStatus::SENT)->count();
        $sentCalls = Call::where('status', ItemStatus::SENT)->count();

        $failedSms = SMS::where('status', ItemStatus::FAILED)->count();
        $failedCalls = Call::where('status', ItemStatus::FAILED)->count();

        return [
            Stat::make('Jami ishlar', $totalWorks)
                ->icon('heroicon-o-briefcase')
                ->color('primary'),

            Stat::make('Jami SMS', $totalSms)
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info'),

            Stat::make('Jami qo\'ng\'iroqlar', $totalCalls)
                ->icon('heroicon-o-phone')
                ->color('warning'),

            Stat::make('Yuborilgan', $sentSms + $sentCalls)
                ->description("SMS: {$sentSms} | Qo'ng'iroq: {$sentCalls}")
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Xatolik', $failedSms + $failedCalls)
                ->description("SMS: {$failedSms} | Qo'ng'iroq: {$failedCalls}")
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}
