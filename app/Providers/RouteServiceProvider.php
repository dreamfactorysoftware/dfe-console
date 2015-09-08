<?php namespace DreamFactory\Enterprise\Console\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Routing\Router;

class RouteServiceProvider extends ServiceProvider
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $namespace = 'DreamFactory\\Enterprise\\Console\\Http\\Controllers';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace],
            function ($router){
                /** @noinspection PhpIncludeInspection */
                require app_path('Http/routes.php');
            });
    }

}
