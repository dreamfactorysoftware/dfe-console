<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Console\Enums\LicenseOperations;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Library\Utility\Curl;
use DreamFactory\Library\Utility\Json;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;

/**
 * A service that talks to the license server
 */
class LicenseServerService extends BaseService
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type array The license server endpoints
     */
    protected $endpoints;
    /**
     * @type string The local DFE installation key
     */
    protected $installKey;
    /**
     * @type bool Connection indicator
     */
    protected $connected = false;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param array $endpoints Alternate endpoints for the license server.
     */
    public function connect(array $endpoints = [])
    {
        if (!$this->connected) {
            if (empty($this->endpoints = empty($endpoints) ? config('license.endpoints') : $endpoints)) {
                throw new \InvalidArgumentException('No valid endpoints available.');
            }

            if (empty($this->installKey = $this->generateInstallKey())) {
                throw new \RuntimeException('No "install-key" found, invalid installation.');
            }

            $this->connected = true;
        }
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
        return $this->post(LicenseOperations::REGISTER_ADMIN, $serviceUser->toArray());
    }

    /**
     * Registers the DFE installation
     *
     * @param \DreamFactory\Enterprise\Database\Models\ServiceUser $serviceUser
     *
     * @return bool|mixed|\stdClass
     */
    public function registerInstall(ServiceUser $serviceUser)
    {
        return $this->post(LicenseOperations::REGISTER_INSTALLATION, $serviceUser->toArray());
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
        return $this->post(LicenseOperations::REGISTER_INSTANCE, $instance->toArray());
    }

    /**
     * Reports anonymous usage statistics
     *
     * @param array $data
     *
     * @return bool|mixed|\stdClass
     */
    public function reportStatistics($data)
    {
        return $this->post(LicenseOperations::REPORT_STATISTICS, $data);
    }

    /**
     * @param string $operation The operation to post
     * @param        $payload
     * @param array  $curlOptions
     *
     * @see LicenseOperations
     * @return bool|mixed|\stdClass
     */
    public function post($operation, $payload, $curlOptions = [])
    {
        //  Make sure we're connected
        $this->connect();

        if (empty($_endpoint = array_get($this->endpoints, $operation))) {
            throw new \InvalidArgumentException('The requested operation has no associated endpoint.');
        }

        empty($payload) && $payload = [];

        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Invalid $payload. It must be an array.');
        }

        //  Jam the install key into the root...
        $payload['install-key'] = $this->installKey;
        $payload = Json::encode($payload);
        $curlOptions[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json; charset=utf8';

        return $this->doPost($_endpoint, $payload, $curlOptions);
    }

    /**
     * @return string
     */
    public function getInstallKey()
    {
        return $this->installKey;
    }

    /**
     * @param string $url
     * @param array  $data
     * @param array  $curlOptions
     *
     * @return bool|mixed|\stdClass
     */
    protected function doPost($url, $data, $curlOptions = [])
    {
        try {
            if (false === ($_result = Curl::post($url, $data, $curlOptions))) {
                throw new \RuntimeException('Network error: ' . json_encode(Curl::getInfo()));
            }

            $_info = Curl::getInfo();

            //  Yay?
            if (Response::HTTP_OK == Curl::getLastHttpCode()) {
                \Log::debug('[dfe.license-server-service:doPost] data posted to ' . $url, $_info);

                return $_result;
            }

            \Log::error('[dfe.license-server-service:doPost] data post failed ' . $url, $_info);
            \Log::debug('[dfe.license-server-service:doPost] * response: ' . print_r($_result, true), $_info);
        } catch (\Exception $_ex) {
            \Log::error('[dfe.license-server-service:doPost] exception reporting usage data: ' . $_ex->getMessage());
        }

        return false;
    }

    /**
     * @return bool|string
     */
    protected function generateInstallKey()
    {
        if (empty($_key = config('metrics.install-key'))) {
            try {
                /** @type ServiceUser $_user */
                $_user = ServiceUser::firstOrFail();

                $_key = sha1(config('provisioning.default-domain') . $_user->getHashedEmail());

                file_put_contents(config_path('metrics.php'),
                    '<?php' .
                    PHP_EOL .
                    '// This file was automatically generated on ' .
                    date('c') .
                    ' by dfe.usage-service' .
                    PHP_EOL .
                    "return ['install-key' => '" .
                    $_key .
                    "',];");
            } catch (ModelNotFoundException $_ex) {
                \Log::notice('No console users found. Nothing to report.');

                return false;
            }

            config(['metrics.install-key' => $_key]);
        }

        return $_key;
    }
}
