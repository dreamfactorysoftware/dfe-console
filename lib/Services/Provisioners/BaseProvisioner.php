<?php namespace DreamFactory\Enterprise\Services\Provisioners;

use DreamFactory\Enterprise\Common\Contracts\ResourceProvisioner;
use DreamFactory\Enterprise\Common\Enums\EnterprisePaths;
use DreamFactory\Enterprise\Common\Services\BaseService;
use DreamFactory\Enterprise\Common\Traits\LockingService;
use DreamFactory\Enterprise\Common\Traits\TemplateEmailQueueing;
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Traits\InstanceValidation;
use DreamFactory\Enterprise\Services\Auditing\Audit;
use DreamFactory\Enterprise\Services\Auditing\Enums\AuditLevels;
use DreamFactory\Enterprise\Services\Providers\ProvisioningServiceProvider;
use DreamFactory\Library\Utility\JsonFile;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Mail\Message;

/**
 * A base class for all provisioners
 *
 * This class provides a foundation upon which to build other PaaS provisioners for the DFE ecosystem. Merely extend
 * the class and add the
 * _doProvision and _doDeprovision methods.
 *
 * @todo Move all english text to templates
 */
abstract class BaseProvisioner extends BaseService implements ResourceProvisioner
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /**
     * @type string This is the "facility" passed along to the auditing system for reporting
     */
    const DEFAULT_FACILITY = ProvisioningServiceProvider::IOC_NAME;

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use InstanceValidation, LockingService, TemplateEmailQueueing;

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type array The default cluster environment file template.
     */
    protected static $envTemplate = [
        'cluster-id'       => null,
        'default-domain'   => null,
        'signature-method' => ConsoleDefaults::SIGNATURE_METHOD,
        'storage-root'     => EnterprisePaths::DEFAULT_HOSTED_BASE_PATH,
        'api-url'          => null,
        'api-key'          => null,
        'client-id'        => null,
        'client-secret'    => null,
    ];
    /**
     * @type string A prefix for notification subjects
     */
    protected $subjectPrefix;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Returns the id, or short name, of this provisioner
     *
     * @return string The provisioner id
     */
    abstract protected function getProvisionerId();

    /**
     * @param ProvisioningRequest|mixed $request
     *
     * @return mixed
     */
    abstract protected function _doProvision($request);

    /**
     * @param ProvisioningRequest|mixed $request
     *
     * @return mixed
     */
    abstract protected function _doDeprovision($request);

    /** @inheritdoc */
    public function boot()
    {
        parent::boot();

        if (empty($this->subjectPrefix)) {
            $this->subjectPrefix = config('dfe.email-subject-prefix', ConsoleDefaults::EMAIL_SUBJECT_PREFIX);
        }

        if (!$this->getLumberjackPrefix()) {
            $this->setLumberjackPrefix(ProvisioningServiceProvider::IOC_NAME . $this->getProvisionerId());
        }
    }

    /** @inheritdoc */
    public function provision($request, $options = [])
    {
        $_timestamp = microtime(true);
        $_result = $this->_doProvision($request);
        $_elapsed = microtime(true) - $_timestamp;

        if (is_array($_result)) {
            $_result['elapsed'] = $_elapsed;
        }

        $this->audit(['elapsed' => $_elapsed, 'result' => $_result]);
        $request->setResult($_result);

        //  Save results...
        $_instance = $request->getInstance();
        $_data = $_instance->instance_data_text ?: [];

        !isset($_data['_operations']) && ($_data['_operations'] = []);
        $_data['_operations'][date('c')] = $_result;
        $_instance->update(['instance_data_text' => $_data]);

        //  Send notification
        $_guest = $_instance->guest;
        $_host =
            ($_guest && $_guest->public_host_text)
                ? $_guest->public_host_text
                : $_instance->instance_id_text . '.' . trim(config('dfe.provisioning.default-dns-zone'), '.') .
                '.' . trim(config('dfe.provisioning.default-dns-domain'), '.');

        $_data = [
            'firstName'     => $_instance->user->first_name_text,
            'headTitle'     => $_result ? 'Launch Complete' : 'Launch Failure',
            'contentHeader' => $_result ? 'Your instance has been launched' : 'Your instance was not launched',
            'emailBody'     =>
                $_result
                    ?
                    '<p>Your instance <strong>' . $_instance->instance_name_text . '</strong> ' .
                    'has been created. You can reach it by going to <a href="//' . $_host . '">' .
                    $_host . '</a> from any browser.</p>'
                    :
                    '<p>Your instance <strong>' .
                    $_instance->instance_name_text .
                    '</strong> ' .
                    'was not created. Our engineers will examine the issue and notify you when it has been resolved. Hang tight, we\'ve got it.</p>',
        ];

        $_subject = $_result['success'] ? 'Instance launch successful' : 'Instance launch failure';

        $this->_notify($_instance, $_subject, $_data);

        return $_result;
    }

    /** @inheritdoc */
    public function deprovision($request, $options = [])
    {
        $_timestamp = microtime(true);
        $_result = $this->_doDeprovision($request);
        $_elapsed = microtime(true) - $_timestamp;
        $_instance = $request->getInstance();

        if (is_array($_result)) {
            $_result['elapsed'] = $_elapsed;
        }

        $this->audit(['elapsed' => $_elapsed, 'result' => $_result]);
        $request->setResult($_result);

        //  Send notification
        $_data = [
            'firstName'     => $_instance->user->first_name_text,
            'headTitle'     => $_result ? 'Shutdown Complete' : 'Shutdown Failure',
            'contentHeader' => $_result ? 'Your instance has retired' : 'Your instance was not retired',
            'emailBody'     => $_result
                ?
                '<p>Your instance <strong>' . $_instance->instance_name_text . '</strong> ' .
                'has been retired.  A snapshot may be available in the dashboard under <strong>Snapshots</strong>.</p>'
                :
                '<p>Your instance <strong>' .
                $_instance->instance_name_text .
                '</strong> shutdown was not successful. Our engineers will examine the issue and, if necessary, notify you if/when the issue has been resolved. Mostly likely you will not have to do a thing. But we will check it out just to be safe.</p>',
        ];

        $_subject = $_result['success'] ? 'Instance shutdown successful' : 'Instance shutdown failure';

        $this->_notify($_instance, $_subject, $_data);

        return $_result;
    }

    /**
     * @param array $data
     * @param int   $level
     * @param bool  $deprovisioning
     *
     * @return bool
     */
    protected function audit($data = [], $level = AuditLevels::INFO, $deprovisioning = false)
    {
        //  Put instance ID into the correct place
        isset($data['instance']) && $data['dfe'] = ['instance_id' => $data['instance']->instance_id_text];

        return Audit::log($data, $level, app('request'), ($deprovisioning ? 'de' : null) . 'provision');
    }

    /**
     * @param Instance $instance
     * @param string   $subject
     * @param array    $data
     *
     * @return int The number of recipients mailed
     */
    protected function _notify($instance, $subject, array $data)
    {
        if (!empty($this->subjectPrefix)) {
            $subject = $this->subjectPrefix . ' ' . trim(str_replace($this->subjectPrefix, null, $subject));
        }

        $_result =
            \Mail::send(
                'emails.generic',
                $data,
                function ($message) use ($instance, $subject) {
                    /** @var Message $message */
                    $message
                        ->to($instance->user->email_addr_text,
                            $instance->user->first_name_text . ' ' . $instance->user->last_name_text)
                        ->subject($subject);
                }
            );

        $this->debug('  * provisioner: notification sent to ' . $instance->user->email_addr_text);

        return $_result;
    }

    /**
     * @param string|Filesystem $filename      The absolute (relative if $filesystem supplied) location to write the
     *                                         environment file
     * @param array             $env           The data to add to the default template
     * @param Filesystem        $filesystem    An optional file system to write the file. If null, the file is written
     *                                         locally
     * @param bool              $mergeDefaults If false, only the data in $env is written out
     *
     * @return bool
     */
    protected function _writeEnvironmentFile($filename, array $env, $filesystem = null, $mergeDefaults = true)
    {
        $_data = $mergeDefaults ? array_merge(static::$envTemplate, $env) : $env;

        if (null !== $filesystem) {
            return $filesystem->put($filename, JsonFile::encode($_data));
        }

        JsonFile::encodeFile($filename, $_data);

        return true;
    }

}