<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Console\Http\Controllers\FactoryController;
use DreamFactory\Enterprise\Services\Providers\UsageServiceProvider;
use Illuminate\Support\Facades\Cache;

class HomeController extends FactoryController
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type int Cache for 5 minutes
     */
    const LINK_CACHE_TTL = 5;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type array The data points collected
     */
    protected $dataPoints = [
        'users'        => 0,
        'admins'       => 0,
        'services'     => 0,
        'ext_services' => 0,
        'apps'         => 0,
    ];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
    }

    /**
     * @return \Illuminate\View\View
     */
    /** @noinspection PhpMissingParentCallCommonInspection */
    public function index()
    {
        //  Check if we've gotten links yet
        if (null === ($_links = \Cache::get('home.links.console'))) {
            $_links = config('links.console', []);

            //  Override links to add link parameters if requested
            foreach ($_links as $_index => $_link) {
                //if (array_get($_link, 'params', false)) {
                    $_links[$_index]['href.og'] = $_links[$_index]['href'];
                    $_links[$_index]['href'] .= '?' . http_build_query($this->getLinkParameters());
                \Log::debug('Home (params) ');

                //}
            }
            \Log::debug('Home (set links) ' . print_r($_links, true));
            \Cache::put('home.links.console', $_links, static::LINK_CACHE_TTL);
        } else {
            \Log::debug('Home (got links) ' . print_r($_links, true));

            //  Restore original links
            foreach ($_links as $_index => $_link) {
                if (isset($_links[$_index]['old-href'])) {
                    $_links[$_index]['href'] = $_links[$_index]['href.og'];
                }
            }
        }

        //  Fill up the expected defaults...
        return $this->renderView('app.home',
            [
                'prefix'       => ConsoleDefaults::UI_PREFIX,
                'resource'     => null,
                'title'        => null,
                'links'        => $_links,
                'request_uri'  => \Request::getRequestUri(),
                'active_class' => ' active',
            ]);
    }

    /**
     * Builds the parameter list to send with any home page links
     *
     * @return array The parameters to send with links
     */
    protected function getLinkParameters()
    {
        $_stats = \App::make(UsageServiceProvider::IOC_NAME)->gatherStatistics();
        $_instanceStats = $this->dataPoints;

        //  Aggregate the instance stats
        foreach (array_get(array_get($_stats, 'instance', []), 'resources', []) as $_key => $_value) {
            if (array_key_exists($_checkKey = $_key . 's', $_instanceStats)) {
                $_instanceStats[$_checkKey] += $_value;
            }
        }

        return [
            'e_k'  => array_get($_stats, 'install-key'),
            'e_u'  => $_stats['console']['user'] + $_stats['dashboard']['user'],
            'e_s'  => $_stats['console']['server'],
            'e_c'  => $_stats['console']['cluster'],
            'e_l'  => $_stats['console']['limit'],
            'e_i'  => $_stats['console']['instance'],
            'i_u'  => $_instanceStats['users'],
            'i_a'  => $_instanceStats['admins'],
            'i_s'  => $_instanceStats['services'],
            'i_es' => $_instanceStats['ext_services'],
            'i_ap' => $_instanceStats['apps'],
        ];
    }
}


