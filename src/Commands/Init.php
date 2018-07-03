<?php

namespace LaravelModule\Commands;

use Illuminate\Console\Command;

class Init extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:init {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化module包(同make:module)';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('make:module', $this->arguments());
    }

}
