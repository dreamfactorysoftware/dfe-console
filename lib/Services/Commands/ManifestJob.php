<?php namespace DreamFactory\Enterprise\Services\Commands;

class ManifestJob extends BaseEnterpriseJob
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    /** @type string My queue */
    const JOB_QUEUE = 'manifest';

    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The absolute path to the output file
     */
    protected $outputFile;
    /**
     * @type bool
     */
    protected $createManifest = true;
    /**
     * @type bool
     */
    protected $showManifest = false;
    /**
     * @type bool
     */
    protected $noKeys = false;
    /** @type string Our handler */
    protected $handlerClass = 'DreamFactory\\Enterprise\\Services\\Handlers\\Commands\\ManifestHandler';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return string
     */
    public function getOutputFile()
    {
        return $this->outputFile;
    }

    /**
     * @param string $outputFile
     *
     * @return $this
     */
    public function setOutputFile($outputFile)
    {
        $this->outputFile = $outputFile;

        return $this;
    }

    public function createManifest()
    {
        return $this->createManifest;
    }

    /**
     * @param boolean $createManifest
     *
     * @return $this
     */
    public function setCreateManifest($createManifest)
    {
        $this->createManifest = $createManifest;

        return $this;
    }

    public function showManifest()
    {
        return $this->showManifest;
    }

    /**
     * @param boolean $showManifest
     *
     * @return $this
     */
    public function setShowManifest($showManifest)
    {
        $this->showManifest = $showManifest;

        return $this;
    }

    /**
     * @return boolean
     */
    public function noKeys()
    {
        return $this->noKeys;
    }

    /**
     * @param bool $noKeys
     *
     * @return $this
     */
    public function setNoKeys($noKeys = false)
    {
        $this->noKeys = $noKeys;

        return $this;
    }
}
