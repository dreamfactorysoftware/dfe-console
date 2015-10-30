<?php
//******************************************************************************
//* Console Bootstrap
//******************************************************************************

use Illuminate\Contracts\Http\Kernel;

if (!function_exists('__dfe_startup')) {
    function __dfe_startup()
    {
        $_path = dirname(__DIR__);

        //  Composer
        require $_path . '/bootstrap/autoload.php';

        //  Laravel
        $_app = require_once $_path . '/bootstrap/app.php';

        /** @type Kernel $_kernel */
        $_kernel = $_app->make('Illuminate\Contracts\Http\Kernel');
        $_response = $_kernel->handle($_request = Illuminate\Http\Request::capture());
        $_response->send();
        $_kernel->terminate($_request, $_response);
    }
}

__dfe_startup();
