<?php

require __DIR__ . '/../vendor/autoload.php';

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * Create the application
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $_app = require __DIR__ . '/../bootstrap/app.php';
        $_app->make( 'Illuminate\Contracts\Console\Kernel' )->bootstrap();

        return $_app;
    }

}