<?php

namespace App\Console\Commands;

use App\Http\Controllers\OraclePushController;
use App\Services\PulloutInterfaceService;
use Illuminate\Console\Command;

class PushMorInterfaceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interface:push-mor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push mor interface every 5 minutes';
    protected $oraclePushController;
    protected $pulloutInterfaceService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(OraclePushController$oraclePushController, PulloutInterfaceService $pulloutInterfaceService)
    {
        parent::__construct();
        $this->oraclePushController = $oraclePushController;
        $this->pulloutInterfaceService = $pulloutInterfaceService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Running push mor interface.');
        $this->oraclePushController->pushMorInterface($this->pulloutInterfaceService);
        $this->info('Done push mor interface.');
    }
}
