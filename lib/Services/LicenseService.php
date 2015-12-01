<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Enterprise\Services\Facades\Usage;
use DreamFactory\Library\Utility\Curl;
use Illuminate\Http\Response;

/**
 * General license services
 */
class LicenseService extends BaseService
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param array $data
     *
     * @return bool|mixed|\stdClass
     */
    public function sendUsageData(array $data = [])
    {
        //  Get url and send data
        if (null === ($_url = config('license.endpoints.usage'))) {
            \Log::warning('[dfe.license] No "usage" license endpoint configured.');

            return false;
        }

        return $this->postData($_url, $data);
    }

    /**
     * Registers the first admin user to identify the DFE installation
     *
     * @param \DreamFactory\Enterprise\Database\Models\ServiceUser $serviceUser
     *
     * @return bool|mixed|\stdClass
     */
    public function registerAdmin(ServiceUser $serviceUser)
    {
        //  Get url and send data
        if (null === ($_url = config('license.endpoints.admin'))) {
            \Log::warning('[dfe.license] No "admin" license endpoint configured.');

            return false;
        }

        return $this->postData($_url, $serviceUser->toArray());
    }

    /**
     * Registers an instance
     *
     * @param \DreamFactory\Enterprise\Database\Models\Instance $instance
     *
     * @return bool|mixed|\stdClass
     */
    public function registerInstance(Instance $instance)
    {
        //  Get url and send data
        if (null === ($_url = config('license.endpoints.instance'))) {
            \Log::warning('[dfe.license] No "instance" license endpoint configured.');

            return false;
        }

        return $this->postData($_url, $instance->toArray());
    }

    /**
     * @param string $url
     * @param array  $data
     * @param array  $curlOptions
     *
     * @return bool|mixed|\stdClass
     */
    protected function postData($url, $data, $curlOptions = [])
    {
        if (false === $this->checkPayload($data)) {
            return false;
        }

        try {
            if (false === ($_result = Curl::post($url, $data, $curlOptions))) {
                \Log::error('[dfe.license] Network communication error.');

                return false;
            }

            //  Yay?
            if (Response::HTTP_OK == Curl::getLastHttpCode()) {
                return $_result;
            }

            \Log::error('[dfe.license] POST failed with status "' .
                Curl::getLastHttpCode() .
                '". Response: ' .
                print_r($_result, true));
        } catch (\Exception $_ex) {
            \Log::error('[dfe.license] Exception while POSTing: ' . $_ex->getMessage());
        }

        return false;
    }

    /**
     * Make sure the payload is kosher
     *
     * @param array $data
     *
     * @return bool
     */
    protected function checkPayload(&$data = [])
    {
        if (empty($data) || !is_array($data)) {
            $data = [];
        }

        try {
            if (empty(array_get($data, 'install-key'))) {
                $data['install-key'] = Usage::service()->generateInstallKey();
            }
        } catch (\Exception $_ex) {
            \Log::warning('[dfe.license] No "root" user.');

            //  No users
            return false;
        }

        return true;
    }
}
