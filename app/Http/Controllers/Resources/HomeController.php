<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Console\Http\Controllers\FactoryController;
use DreamFactory\Enterprise\Database\Models\Metrics;
use DreamFactory\Enterprise\Services\Providers\UsageServiceProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
                //  Only show links that are supposed to be shown...
                if (array_get($_link, 'show', false)) {
                    $_links[$_index]['href.og'] = $_links[$_index]['href'];

                    if ($_links[$_index]['name'] == 'Licensing') {
                        $_links[$_index]['href'] .= '?' . http_build_query($_params = $this->getLinkParameters());
                    }
                }
            }

            \Cache::put('home.links.console', $_links, static::LINK_CACHE_TTL);

            //  Mark metrics as being sent
            !empty($_params) && Metrics::where('sent_ind', 0)->update(['sent_ind' => 1]);
        } else {
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
        /** @noinspection PhpUndefinedMethodInspection */
        return [
            'e_k' => UsageServiceProvider::service()->generateInstallKey(),
        ];
    }
}


