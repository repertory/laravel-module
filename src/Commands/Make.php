<?php

namespace LaravelModule\Commands;

use Laravel\Lumen\Application as LumenApplication;
use Illuminate\Console\GeneratorCommand;

class Make extends GeneratorCommand
{

    protected $name = 'make:module';

    protected $description = 'Create a new Module';

    protected $type = 'Module';

    protected function getStub()
    {
        return __DIR__ . '/stubs/controller.stub';
    }

    protected function rootNamespace()
    {
        return $this->type;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    protected function getNamespace($name)
    {
        return trim(implode('\\', array_map(function ($str) {
            return studly_case(strtolower($str));
        }, explode('\\', $name))), '\\');
    }

    protected function getPath($name)
    {
        $name = str_replace_first($this->rootNamespace(), '', $name);
        return dirname($this->laravel['path']) . '/module/' . trim(str_replace('\\', '/', $name), '/') . '/src/Controller.php';
    }

    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $rootNamespace = $this->rootNamespace();

        if (starts_with($name, $rootNamespace)) {
            return $name;
        }

        $name = str_replace('/', '\\', $name);

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name
        );
    }

    public function handle()
    {
        $name = $this->qualifyClass($this->getNameInput());

        if (!preg_match("#^[a-z]+(_?[a-z]+[0-9]{0,}){0,}\/[a-z]+(_?[a-z]+[0-9]{0,}){0,}$#", $this->getNameInput())) {
            $this->error($this->type . ' name error! FORMAT: [group/module | group_name/module_name]');
            return false;
        }
        if ($this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . ' already exists!');
            return false;
        }

        $path = $this->getPath($name);
        $composer = dirname(dirname($path)) . '/composer.json';
        $view = dirname(dirname($path)) . '/views/index.blade.php';

        $this->makeDirectory($path);
        $this->makeDirectory($view);
        $this->files->copy(dirname(dirname(__DIR__)) . '/.editorconfig', dirname(dirname($path)) . '/.editorconfig');
        $this->files->put($path, $this->buildClass($name));
        $this->files->put($view, $this->getNameInput());
        $this->files->put($composer, str_replace('\/', '/', json_encode([
            'name' => strtolower($this->getNameInput()),
            'description' => 'module for laravel-module',
            'type' => 'laravel-module',
            'authors' => [
                [
                    'name' => 'author name',
                    'email' => 'author@email'
                ],
            ],
            'autoload' => [
                'psr-4' => [
                    $this->getNamespace($name) . '\\' => 'src/'
                ]
            ],
            'extra' => [
                'laravel-module' => [
                    'config' => [
                        'name' => strtolower($this->getNameInput())
                    ],
                    'middleware' => [],
                    'module' => [
                        'name' => strtolower($this->getNameInput()),
                        'time' => date('c'),
                        'from' => [
                            'php' => PHP_VERSION,
                            'framework' => (app() instanceof LumenApplication) ? 'lumen' : 'laravel',
                            'version' => app()->version(),
                        ],
                    ],
                ]
            ]
        ], JSON_UNESCAPED_UNICODE)));

        @exec('composer dumpautoload');
        $this->info($this->type . ' created successfully.');
    }

}
