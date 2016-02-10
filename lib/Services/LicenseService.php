<?php namespace DreamFactory\Enterprise\Services;

use DreamFactory\Enterprise\Database\Models\Instance;
use DreamFactory\Enterprise\Database\Models\ServiceUser;

/**
 * General license services
 */
class LicenseService extends BaseLicenseService
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
     * Registers the DFE installation
     *
     * @param \DreamFactory\Enterprise\Database\Models\ServiceUser $serviceUser
     *
     * @return bool|mixed|\stdClass
     */
    public function registerInstall(ServiceUser $serviceUser)
    {
        //  Get url and send data
        if (null === ($_url = config('license.endpoints.install'))) {
            \Log::warning('[dfe.license] No "install" license endpoint configured.');

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
}
