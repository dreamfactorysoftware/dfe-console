<?php namespace DreamFactory\Enterprise\Console\Console\Commands;

use DreamFactory\Enterprise\Common\Commands\ConsoleCommand;
use DreamFactory\Enterprise\Common\Enums\EnterpriseDefaults;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Common\Traits\Guzzler;
use DreamFactory\Library\Utility\Curl;
use DreamFactory\Library\Utility\Json;
use DreamFactory\Library\Utility\Uri;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Http\Request;
use Symfony\Component\Console\Input\InputArgument;

class Blueprint extends ConsoleCommand implements SelfHandling
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup, Guzzler;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @inheritdoc */
    protected $name = 'dfe:blueprint';
    /** @inheritdoc */
    protected $description = 'Generates an instance blueprint';
    /**
     * @type string Our session token
     */
    protected $token;
    /**
     * @type string Our endpoint
     */
    protected $endpoint;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    public function fire()
    {
        parent::fire();

        try {
            $this->endpoint = rtrim($this->argument('instance-uri'), '/ ') . '/api/v2';

            $_payload = [
                'email'       => $this->argument('admin-email'),
                'password'    => $this->argument('admin-password'),
                'remember_me' => false,
            ];

            $_result = $this->api('/system/admin/session', $_payload);

            if (!is_object($_result) || !isset($_result->session_token)) {
                $this->writeln('response : ' . print_r($_result, true));
                throw new \Exception('Invalid response.');
            }

            $this->token = $_result->session_token;

            //  Get services
            $_result = $this->api('/system?as_list=true', [], [], Request::METHOD_GET);

            $this->writeln('response : ' . print_r($_result, true));

            return true;
        } catch (\Exception $_ex) {
            $this->error($_ex->getMessage());

            return false;
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(),
            [
                ['instance-uri', InputArgument::REQUIRED, 'The URI of the instance (i.e. "http://localhost")'],
                ['admin-email', InputArgument::REQUIRED, 'An instance administrator email'],
                ['admin-password', InputArgument::REQUIRED, 'An instance administrator password'],
            ]);
    }

    /**
     * Makes a shout out to an instance's private back-end. Should be called bootyCall()  ;)
     *
     * @param string $uri     The REST uri (i.e. "/[rest|api][/v[1|2]]/db", "/rest/system/users", etc.) to retrieve
     *                        from the instance
     * @param array  $payload Any payload to send with request
     * @param array  $options Any options to pass to transport layer
     * @param string $method  The HTTP method. Defaults to "POST"
     *
     * @return array|bool|\stdClass
     */
    protected function api($uri, $payload = [], $options = [], $method = Request::METHOD_POST)
    {
        $options[CURLOPT_HTTPHEADER] = array_merge(array_get($options, CURLOPT_HTTPHEADER, []),
            ['Content-Type: application/json', EnterpriseDefaults::INSTANCE_X_HEADER . ': ' . $this->token]);

        try {
            $_response =
                Curl::request($method, Uri::segment([$this->endpoint, $uri], false), Json::encode($payload), $options);
        } catch (\Exception $_ex) {
            return false;
        }

        return $_response;
    }
}
