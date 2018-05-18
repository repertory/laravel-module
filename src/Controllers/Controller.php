<?php

namespace LaravelModule\Controllers;

use Laravel\Lumen\Application as LumenApplication;

if (app() instanceof LumenApplication) {
    class Controller extends LumenController
    {
        //
    }
} else {
    class Controller extends LaravelController
    {
        //
    }
}
