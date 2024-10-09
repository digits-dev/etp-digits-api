<?php

namespace App\Console\Commands;

use App\Http\Controllers\OraclePushController;
use App\Services\DeliveryInterfaceService;
use Illuminate\Console\Command;

class PushSitInterfaceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interface:push-sit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push sit interface every 5 minutes';
    protected $oraclePushController;
    protected $deliveryInterfaceService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(OraclePushController $oraclePushController, DeliveryInterfaceService $deliveryInterfaceService)
    {
        parent::__construct();
        $this->oraclePushController = $oraclePushController;
        $this->deliveryInterfaceService = $deliveryInterfaceService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Running push sit interface.');
        $this->oraclePushController->pushSitInterface($this->deliveryInterfaceService);
        $this->info('Done push sit interface.');
    }
}
