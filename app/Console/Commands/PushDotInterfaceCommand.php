<?php

namespace App\Console\Commands;

use App\Http\Controllers\OraclePushController;
use App\Services\DeliveryInterfaceService;
use Illuminate\Console\Command;

class PushDotInterfaceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interface:push-dot-dotr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push dot interface every 5 minutes';
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
        $this->info('Running push dot interface.');
        $this->oraclePushController->pushDotInterface($this->deliveryInterfaceService);
        $this->info('Done push dot interface.');

        $this->info('Running push dotr interface.');
        $this->oraclePushController->pushDotrInterface($this->deliveryInterfaceService);
        $this->info('Done push dotr interface.');
    }
}
