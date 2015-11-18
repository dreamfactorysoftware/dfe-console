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

    /**
     * Gets the home page links
     *
     * @return array
     */
    protected function getConsoleLinks()
    {
        $_links = [];
        $_rawLinks = config('links.console', []);
        \Log::debug('Raw links: ' . print_r($_rawLinks, true));

        //  Override links to add link parameters if requested
        foreach ($_rawLinks as $_index => $_link) {
            //  Don't show control links or first-user links
            if (array_get($_link, 'show', false) || 'first_user' == $_link['name']) {
                continue;
            }

            if ('Licensing' == $_link['name']) {
                $_link['href'] .= '?' . http_build_query($_params = $this->getLinkParameters());
            }

            //  Only show links that are supposed to be shown...
            $_links[] = $_link;
        }

        return $_links;
    }
}
