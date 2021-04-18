<?php

namespace devsrv\inplace\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inplace:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate advanced extended configurator for inplace fields';

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
        $file = app_path('Providers/InplaceConfigServiceProvider.php');

        if(File::exists($file)) {
            $this->info('🙄 File already exists at App\Providers');
            $this->newLine();
            $this->info('to publish again safely backup the current file & rename it or put somewhere else');
            $this->info('then try again');

            return 1;
        }

        $stub_contents = File::get(__DIR__ . '/../../resources/stubs/InplaceConfigServiceProvider.stub');
        File::put($file, $stub_contents);

        $this->info('🥳 Config published successfully!');
        $this->newLine();
        $this->info('👉 don\'t forget to add App\Providers\InplaceConfigServiceProvider::class');
        $this->info('👉 in the providers list of app.php');

        return 1;
    }
}
