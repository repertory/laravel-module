<?php

namespace LaravelModule\Commands;

use Illuminate\Console\Command;

class Publish extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish any publishable assets from module packages';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('vendor:publish', ['--provider' => 'LaravelModule\\ServiceProvider']);
    }

}
