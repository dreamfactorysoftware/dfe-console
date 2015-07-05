<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;

class HomeController extends FactoryController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return $this->renderView(
            'app.dashboard',
            [
                '_trail'  => null,
                '_active' => ['instances' => 0, 'servers' => 0, 'users' => 0, 'clusters' => 0],
                'prefix'  => ConsoleDefaults::UI_PREFIX,
            ]
        );
    }

}
