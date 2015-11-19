<?php namespace DreamFactory\Enterprise\Services\Telemetry;

use Carbon\Carbon;
use DreamFactory\Enterprise\Common\Exceptions\NotImplementedException;
use DreamFactory\Enterprise\Database\Models\Cluster;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\Limit;
use DreamFactory\Enterprise\Database\Models\Mount;
use DreamFactory\Enterprise\Database\Models\Server;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Enterprise\Services\Contracts\ProvidesTelemetry;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Schema;

abstract class BaseTelemetryProvider implements ProvidesTelemetry
{
    //******************************************************************************
    //* Constant
    //******************************************************************************

    /**
     * @type string Our provider ID
     */
    const PROVIDER_ID = false;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    abstract public function getTelemetry($options = []);

    /**
     * @return string
     * @throws \DreamFactory\Enterprise\Common\Exceptions\NotImplementedException
     */
    public function getProviderId()
    {
        if (!static::PROVIDER_ID) {
            throw new NotImplementedException('The PROVIDER_ID must be overridden in your sub-class.');
        }

        return static::PROVIDER_ID;
    }
}
