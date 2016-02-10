<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\ServiceUser;
use DreamFactory\Enterprise\Services\Facades\Usage;
use DreamFactory\Library\Utility\Curl;
use DreamFactory\Library\Utility\Json;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;

/**
 * A base class for license services
 */
abstract class BaseLicenseService extends BaseService
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The installation key
     */
    protected $installKey;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /** @inheritdoc */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->installKey = $this->generateInstallKey();
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
        //  Jam the install key into the root...
        if (!empty($data) && !is_scalar($data)) {
            !isset($data['install-key']) && $data['install-key'] = $this->generateInstallKey();
            $data = Json::encode($data);
        }

        $curlOptions = array_merge($curlOptions, [CURLOPT_HTTPHEADER => ['Content-Type: application/json']]);

        try {
            if (false === ($_result = Curl::post($url, $data, $curlOptions))) {
                throw new \RuntimeException('Network error');
            }

            //  Yay?
            if (Response::HTTP_OK == Curl::getLastHttpCode()) {
                \Log::debug('[dfe.license-service:postData] data posted to ' . $url, Curl::getInfo());

                return $_result;
            }

            \Log::error('[dfe.license-service:postData] data post failed ' . $url, Curl::getInfo());
            \Log::debug('[dfe.license-service:postData] * response: ' . print_r($_result, true), Curl::getInfo());
        } catch (\Exception $_ex) {
            \Log::error('[dfe.license-service:postData] exception reporting usage data: ' . $_ex->getMessage());
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function generateInstallKey()
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
