<?php

namespace devsrv\inplace;

use Exception;
use Illuminate\View\Factory as ViewFactory;
use Livewire\Exceptions\ComponentNotFoundException;
use Illuminate\Support\Str;

class RenderBlade {
    protected $viewFactory;

    public function __construct(ViewFactory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }

    public function resolveComponent(string $contents, string $name, array $data = []) {
        $componentStr = str_replace('%component%', Str::kebab($name), $contents);

        try {
            $markup = $this->render($componentStr, $data);
        } catch (ComponentNotFoundException $e) {
            throw new \Exception('Custom Editable Component Not Found !');
        }

        return $markup;
    }

    protected function render(string $contents, array $data) {
        file_put_contents(
            $path = tempnam(sys_get_temp_dir(), 'blade-on-demand') . '.blade.php',
            $contents
        );
    
        $this->viewFactory->flushFinderCache();

        return  $this->viewFactory->file($path, $data)->render();
    }
}