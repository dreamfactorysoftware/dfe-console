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





        $_inst['users'] = 0;
        $_inst['admins'] = 0;
        $_inst['services'] = 0;
        $_inst['ext_services'] = 0;
        $_inst['apps'] = 0;


        foreach ($_stats['instance'] as $key => $value) {

            foreach ($value as $k => $v) {

                if($k == 'resources') {
                    foreach ($v as $k1 => $v1) {
                        if ($k1 == 'user') {
                            $_inst['users'] += $v1;
                        }

                        if ($k1 == 'admin') {
                            $_inst['admins'] += $v1;
                        }

                        if ($k1 == 'service') {
                            $_inst['services'] += intval($v1);
                        }

                        /*
                        if ($k1 == 'admin') {
                            $_inst['ext_services'] += $v1;
                        }
                        */

                        if ($k1 == 'app') {
                            $_inst['apps'] += $v1;
                        }
                    }
                }
            }
        }

        $_formatted_stats = [
            'e_u' => $_stats['console']['user'] + $_stats['dashboard']['user'],
            'e_s' => $_stats['console']['server'],
            'e_c' => $_stats['console']['cluster'],
            'e_l' => $_stats['console']['limit'],
            'e_i' => $_stats['console']['instance'],
            'i_u' => $_inst['users'],
            'i_a' => $_inst['admins'],
            'i_s' => $_inst['services'],
            'i_es' => $_inst['ext_services'],
            'i_ap' => $_inst['apps']
        ];


        //  Fill up the expected defaults...
        return $this->renderView('app.home',
            [
                'prefix'   => ConsoleDefaults::UI_PREFIX,
                'resource' => null,
                'title'    => null,
                'links'    => config('links.console', []),
                'stats'    => http_build_query($_formatted_stats)
            ]);
    }

}


