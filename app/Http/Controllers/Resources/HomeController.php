<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Console\Http\Controllers\ViewController;

use DreamFactory\Enterprise\Services\Providers\UsageServiceProvider;
use DreamFactory\Enterprise\Services\UsageService;
use DreamFactory\Library\Utility\Json;

class HomeController extends ViewController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {


        $_service = \App::make(UsageServiceProvider::IOC_NAME);
        $_stats = $_service->gatherStatistics();




        //  Fill up the expected defaults...
        return $this->renderView('app.home',
            [
                'prefix'   => ConsoleDefaults::UI_PREFIX,
                'resource' => null,
                'title'    => null,
                'links'    => config('links.console', []),
                'stats'    => $_stats['console']
            ]);
    }

}


