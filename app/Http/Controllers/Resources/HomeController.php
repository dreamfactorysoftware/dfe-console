<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Console\Http\Controllers\ViewController;

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
        //  Fill up the expected defaults...
        return $this->renderView('app.home',
            [
                'prefix'   => ConsoleDefaults::UI_PREFIX,
                'resource' => null,
                'title'    => null,
                'links'    => config('links.console', []),
            ]);
    }

}
