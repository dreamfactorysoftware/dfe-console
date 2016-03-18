<?php namespace DreamFactory\Enterprise\Services\Telemetry;

use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\Limit;
use DreamFactory\Enterprise\Database\Models\Mount;
use DreamFactory\Enterprise\Database\Models\Server;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use Illuminate\Support\Facades\Request;

class ConsoleTelemetry extends BaseTelemetryProvider
{
    //******************************************************************************
    //* Constant
    //******************************************************************************

    /**
     * @type string Our provider ID
     */
    const PROVIDER_ID = 'console';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type array All tables
     */
    protected $tables = [];
    /**
     * @type array Only these tables
     */
    protected $only = ['user_t', 'mount_t', 'server_t', 'cluster_t', 'limit_t', 'instance_t', 'service_user_t',];
    /**
     * @type array All but these tables
     */
    protected $except = [];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function getTelemetry($options = [])
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $_stats = [
            'uri'       => $_uri = config('app.url', Request::getSchemeAndHttpHost()),
            'resources' => [
                'user'     => ServiceUser::count(),
                'mount'    => Mount::count(),
                'server'   => Server::count(),
                'cluster'  => Cluster::count(),
                'limit'    => Limit::count(),
                'instance' => Instance::count(),
            ],
        ];

        logger('[dfe.telemetry.console] ** ' . $_uri);

        return $_stats;
    }
}
