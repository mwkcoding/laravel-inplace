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
    protected $signature = 'inplace:config
                            {type=all : The type of inplace editable}';

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

    private function validateType() {
        $supports = ['all', 'relation', 'inline'];

        $type = $this->argument('type');

        if(! in_array($type, $supports)) {
            $this->error('😱 supported types: '. implode(', ', $supports));
            return false;
        }

        return $type;
    }

    private function createConfigFile($type) {
        $path = [
            'inline' => [
                'target_file' => app_path('Http/Inplace/Inline.php'),
                'target_file_path' => 'App/Http/Inplace/Inline.php',
                'stub' => File::get(__DIR__ . '/../../resources/stubs/Inline.stub')
            ],
            'relation' => [
                'target_file' => app_path('Http/Inplace/Relation.php'),
                'target_file_path' => 'App/Http/Inplace/Relation.php',
                'stub' => File::get(__DIR__ . '/../../resources/stubs/Relation.stub')
            ]
        ];

        if(File::exists(data_get($path, $type.'.target_file'))) {
            $this->error('🙄 '. $type .' configurator already exists at '. data_get($path, $type.'.target_file_path'));
            $this->info('to publish again safely backup the current file & rename it or put somewhere else');
            $this->info('then try again');
            $this->newLine();

            return;
        }

        File::put(data_get($path, $type.'.target_file'), data_get($path, $type.'.stub'));
        $this->info('🥳 '. $type .' config published successfully!');
        $this->info('👉 '. data_get($path, $type.'.target_file_path'));
        $this->newLine();

        return;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if(! $type = $this->validateType()) return;

        $inplace_path = app_path('Http/Inplace');

        if (! is_dir($inplace_path)) File::makeDirectory($inplace_path, 0755, true);


        if($type === 'all') {
            $this->createConfigFile('inline');
            $this->createConfigFile('relation');

            return;
        }

        $this->createConfigFile($type);

        return;
    }
}
