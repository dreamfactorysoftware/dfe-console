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


        foreach ($_stats['instance'] as $i => $instance) {

            foreach ($instance as $type => $resource) {

                if($type == 'resources') {
                    foreach ($resource as $resource_type => $resource_value) {
                        if ($resource_type == 'user') {
                            $_inst['users'] += $resource_value;
                        }

                        if ($resource_type == 'admin') {
                            $_inst['admins'] += $resource_value;
                        }

                        if ($resource_type == 'service') {
                            $_inst['services'] += intval($resource_value);
                        }

                        if ($resource_type == 'ext_services') {
                            $_inst['ext_services'] += $resource_value;
                        }

                        if ($resource_type == 'app') {
                            $_inst['apps'] += $resource_value;
                        }
                    }
                }
            }
        }

        $_formatted_stats = [
            'e_k' => 1, //$_stats['install-key'],
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


        $_links = config('links.console', []);
        $_links[0]['href'] .= '?'.http_build_query($_formatted_stats);

        //  Fill up the expected defaults...
        return $this->renderView('app.home',
            [
                'prefix'   => ConsoleDefaults::UI_PREFIX,
                'resource' => null,
                'title'    => null,
                'links'    => $_links
            ]);
    }

}


