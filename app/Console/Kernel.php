<?php

namespace App\Console;

use App\Http\Controllers\OraclePullController;
use App\Services\ItemSyncService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        \App\Console\Commands\MakeService::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->call(function(){
            $oracle = new OraclePullController();
            $oracle->processOrgTransfers();
            $oracle->processReturnTransactions();
            $oracle->updateOracleItemId();
        })->everyFiveMinutes();

        $schedule->call(function(){
            $oracle = new OraclePullController();
            $oracle->moveOrderPull(request());
            $oracle->salesOrderPull(request());

            app(ItemSyncService::class)->syncNewItems(request());
            app(ItemSyncService::class)->syncUpdatedItems(request());
        })->hourly();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
