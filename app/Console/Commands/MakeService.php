<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

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
        $name = $this->argument('name');
        $this->createService($name);
        $this->info("Service $name created successfully.");
    }
    protected function createService($name)
    {
        $directory = app_path('Services');
        $path = $directory . "/{$name}.php";

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (File::exists($path)) {
            $this->error("Service $name already exists!");
            return;
        }

        $stub = $this->getStub();
        $stub = str_replace('{{name}}', $name, $stub);

        File::put($path, $stub);
    }

    protected function getStub()
    {
        return file_get_contents(resource_path('stubs/service.stub'));
    }
}
