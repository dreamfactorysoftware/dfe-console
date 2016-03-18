<?php namespace DreamFactory\Enterprise\Services\Telemetry;

use Carbon\Carbon;
use DreamFactory\Enterprise\Common\Enums\InstanceStates;
use DreamFactory\Enterprise\Database\Exceptions\InstanceNotActivatedException;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\MetricsDetail;
use DreamFactory\Enterprise\Instance\Ops\Facades\InstanceApiClient;
use DreamFactory\Enterprise\Instance\Ops\Services\InstanceApiClientService;
use Exception;
use Log;

class InstanceTelemetry extends BaseTelemetryProvider
{
    //******************************************************************************
    //* Constant
    //******************************************************************************

    /**
     * @type string Our provider ID
     */
    const PROVIDER_ID = 'instance';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function getTelemetry($options = [])
    {
        $_gathered = 0;
        $_gatherDate = date('Y-m-d');
        $_metrics = null;
        $_start = array_get($options, 'start');

        $_instances = !empty($_start) ? Instance::where('id >= :id', $_start)->orderBy('id')->get() : Instance::orderBy('id')->get();

        /** @type Instance $_instance */
        foreach ($_instances as $_instance) {
            $_api = InstanceApiClient::connect($_instance);

            //  Seed the stats, defaults to "not activated"
            $_stats = [
                'uri'         => $_api->getProvisionedEndpoint(),
                'environment' => ['version' => null, 'inception' => $_instance->create_date, 'status' => 'not activated'],
                'resources'   => [],
            ];

            try {
                if (false !== ($_status = $_api->determineInstanceState(true))) {
                    array_set($_stats, 'environment.version', data_get($_status, 'platform.version_current'));

                    //  Does it appear ok?
                    $_instance = $_instance->fresh();

                    switch ($_instance->ready_state_nbr) {
                        case InstanceStates::READY:
                            $_stats['environment']['status'] = 'activated';
                            break;

                        case InstanceStates::ADMIN_REQUIRED:
                            $_stats['environment']['status'] = 'no admin';
                            break;

                        default:
                            $_stats['environment']['status'] = 'not activated';
                            break;
                    }

                    //  Get resource counts
                    if (InstanceStates::INIT_REQUIRED != $_instance->ready_state_nbr && false === ($_stats['resources'] = $this->getResourceCounts($_api))) {
                        $_stats['resources'] = [];
                    }
                }
            } catch (InstanceNotActivatedException $_ex) {
                //  Instance unavailable or not initialized
            } catch (Exception $_ex) {
                //  Instance unavailable or not initialized
                array_set($_stats, 'environment.status', 'error');
            }

            logger('[dfe.telemetry.instance] > ' . $_stats['environment']['status'] . ' ' . $_instance->id . ':' . $_instance->instance_id_text);

            try {
                /** @type MetricsDetail $_row */
                $_row = MetricsDetail::firstOrCreate(['user_id' => $_instance->user_id, 'instance_id' => $_instance->id, 'gather_date' => $_gatherDate]);
                $_row->data_text = $_stats;
                $_row->save();

                $_gathered++;
            } catch (Exception $_ex) {
                Log::error('[dfe.telemetry.instance] ' . $_ex->getMessage());
            }

            unset($_api, $_stats, $_list, $_status, $_row, $_instance);
        }

        Log::info('[dfe.telemetry.instance] ' . number_format($_gathered, 0) . ' instance(s) examined.');

        return $this->aggregateMetrics($_gatherDate);
    }

    /**
     * Iterates through, and counts an instance's resources
     *
     * @param InstanceApiClientService $client
     *
     * @return array
     */
    protected function getResourceCounts($client)
    {
        $_list = [];

        if (false === ($_resources = $client->resources()) || empty($_resources)) {
            return false;
        }

        foreach ($_resources as $_resource) {
            try {
                if (false !== ($_result = $client->resource($_resource))) {
                    $_list[$_resource] = count($_result);
                } else {
                    $_list[$_resource] = 0;
                }
            } catch (Exception $_ex) {
                $_list[$_resource] = 'error';
            }
        }

        return $_list;
    }

    /**
     * @param string|Carbon $date
     *
     * @return array [];
     */
    protected function aggregateMetrics($date)
    {
        $_detailed = config('license.send-instance-details', false);
        $_gathered = $_resourceCounts = $_versions = $_states = [];

        //  Pull all the details up into a single array and return it
        /** @noinspection PhpUndefinedMethodInspection */
        foreach (MetricsDetail::byGatherDate($date)->with('instance')->get() as $_detail) {
            $_metrics = $_detail->data_text;

            //  Aggregate versions
            if (!empty($_version = data_get($_metrics, 'environment.version'))) {
                !array_key_exists($_version, $_versions) && $_versions[$_version] = 0;
                $_versions[$_version]++;
            }

            //  Aggregate statuses
            if (!empty($_state = data_get($_metrics, 'environment.status'))) {
                !array_key_exists($_state, $_states) && $_states[$_state] = 0;
                $_states[$_state]++;
            }

            //  Aggregate resource counts
            foreach (data_get($_metrics, 'resources', []) as $_resource => $_count) {
                !array_key_exists($_resource, $_resourceCounts) && $_resourceCounts[$_resource] = 0;
                is_numeric($_count) && $_resourceCounts[$_resource] += $_count;
            }

            $_detailed && $_gathered[$_detail->instance->instance_id_text] = $_metrics;
        }

        return array_merge($_gathered,
            [
                '_aggregated' => [
                    'versions'  => $_versions,
                    'resources' => $_resourceCounts,
                    'states'    => $_states,
                ],
            ]);
    }
}
