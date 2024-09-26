<?php

namespace App\Console\Commands;

use App\Http\Controllers\OraclePullController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TaskOraclePullCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:orderpull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute the scheduled task to pull Oracle orders.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Add console output message
        $this->info('Scheduled task started: Oracle Pull Task.');

        // Perform the task
        $result = $this->performOrderPull();

        // Add console output and log message
        if ($result) {
            $this->info('Task completed successfully: Oracle Pull Task.');
            Log::info('Task completed successfully: Oracle Pull Task.');
        } else {
            $this->error('Task failed: Oracle Pull Task.');
            Log::error('Task failed: Oracle Pull Task.');
        }
    }

    private function performOrderPull()
    {
        $oracle = new OraclePullController();
        $oracle->moveOrderPull(request());
        $oracle->salesOrderPull(request());
        return true;
    }
}
