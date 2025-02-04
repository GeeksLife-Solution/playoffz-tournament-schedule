<?php

namespace App\Console;

use App\Console\Commands\BinanceGetStatus;
use App\Console\Commands\InvestUpdate;
use App\Console\Commands\PayoutCryptoCurrencyUpdateCron;
use App\Console\Commands\PayoutCurrencyUpdateCron;
use App\Models\Deposit;
use App\Models\Payout;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{


    protected $commands = [
        PayoutCurrencyUpdateCron::class,
        PayoutCryptoCurrencyUpdateCron::class,
        InvestUpdate::class,
        BinanceGetStatus::class
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $basicControl = basicControl();

        $schedule->command('invest:run')->hourly();
        $schedule->command('payout-status:update')->hourly();

        if ($basicControl->currency_layer_auto_update == 1) {
            $schedule->command('payout-currency:update')
                ->{basicControl()->currency_layer_auto_update_at}();
        }
        if ($basicControl->coin_market_cap_auto_update == 1) {
            $schedule->command('payout-crypto-currency:update')->{basicControl()->coin_market_cap_auto_update_at}();
        }

        $schedule->command('model:prune', [
            '--model' => [
                Deposit::class,
                Payout::class,
            ],
        ])->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
