<?php

namespace App\Console;

use App\Http\Controllers\OraclePullController;
use App\Http\Controllers\OraclePushController;
use App\Services\ItemSyncService;
use App\Services\WarehouseSyncService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        \App\Console\Commands\MakeService::class,
        \App\Console\Commands\PushDotInterfaceCommand::class,
        // \App\Console\Commands\TaskOraclePullCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('task:orderpull')->hourly();
        // $schedule->command('interface:push-dot-dotr')->everyFifteenMinutes();

        $schedule->call(function(){

            $oracle = new OraclePullController();
            $oracle->moveOrderPull(request());
            $oracle->salesOrderPull(request());

            $datefrom = Carbon::now()->format("Y-m-d");
            $dateto = Carbon::now()->addDays(1)->format("Y-m-d");

            $itemSync = new ItemSyncService();
            $itemSync->syncNewItems(request()->merge(['datefrom'=>$datefrom,'dateto'=>$dateto]));
            $itemSync->syncUpdatedItems(request()->merge(['datefrom'=>$datefrom,'dateto'=>$dateto]));

            $whSync = new WarehouseSyncService();
            $whSync->syncNewWarehouse(request()->merge(['datefrom'=>$datefrom,'dateto'=>$dateto]));
        })->hourly();

        $schedule->call(function(){
            $oracle = new OraclePullController();
            $oracle->processOrgTransfers();
            $oracle->processOrgTransfersReceiving();//for dotr
            $oracle->processSubInvTransfersReceiving();//for sitr
            $oracle->processReturnTransactions();
            $oracle->updateOracleItemId();
        })->everyMinute();

        $schedule->call(function(){
            $oracle = new OraclePullController();
            $oracle->updateOrgTransfers();
        })->everyFiveMinutes()->between('01:00:00', '05:00:00');

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
