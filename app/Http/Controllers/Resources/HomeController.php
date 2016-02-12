<?php namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Console\Http\Controllers\FactoryController;
use DreamFactory\Enterprise\Services\Facades\License;
use DreamFactory\Enterprise\Services\Facades\Usage;

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

        $this->middleware('auth', ['except' => ['softLaunch']]);
    }

    /**
     * @return \Illuminate\View\View
     */
    /** @noinspection PhpMissingParentCallCommonInspection */
    public function index()
    {
        //  Fill up the expected defaults...
        return $this->renderView('app.home',
            [
                'prefix'       => ConsoleDefaults::UI_PREFIX,
                'resource'     => null,
                'title'        => null,
                'links'        => $this->getConsoleLinks(),
                'request_uri'  => \Request::getRequestUri(),
                'active_class' => ' active',
            ]);
    }

    public function logout()
    {
        \Auth::logout();

        return \Redirect::guest('auth/login')->with('errors', 'Your session has expired or is otherwise not valid.');
    }

    public function softLaunch()
    {
        return view('soft-launch.layout');
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
            'e_k' => License::getInstallKey(),
        ];
    }

    /**
     * Gets the home page links
     *
     * @return array
     */
    protected function getConsoleLinks()
    {
        $_links = [];
        $_rawLinks = config('links.console', []);

        //  Override links to add link parameters if requested
        foreach ($_rawLinks as $_link) {
            //  Don't show control links or first-user links
            if (array_key_exists('show', $_link) && true !== $_link['show']) {
                continue;
            }

            //  Licensing has parameters
            if ('Licensing' == $_link['name']) {
                $_link['href'] .= '?' . http_build_query($_params = $this->getLinkParameters());
            }

            //  Only show links that are supposed to be shown...
            $_links[] = $_link;
        }

        return $_links;
    }
}
