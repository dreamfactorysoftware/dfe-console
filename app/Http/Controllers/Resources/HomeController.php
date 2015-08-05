<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Console\Http\Controllers\ResourceController;


class HomeController extends ResourceController
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
            ]
        );
    }

}
