<?php namespace DreamFactory\Enterprise\Services\Commands;

use DreamFactory\Enterprise\Common\Commands\EnterpriseJobCommand;

class ManifestJob extends EnterpriseJobCommand
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
    protected $_outputFile;
    /**
     * @type bool
     */
    protected $_createManifest = true;
    /**
     * @type bool
     */
    protected $_showManifest = false;
    /**
     * @type bool
     */
    protected $_noKeys = false;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return string
     */
    public function getOutputFile()
    {
        return $this->_outputFile;
    }

    /**
     * @param string $outputFile
     *
     * @return $this
     */
    public function setOutputFile( $outputFile )
    {
        $this->_outputFile = $outputFile;

        return $this;
    }

    public function createManifest()
    {
        return $this->_createManifest;
    }

    /**
     * @param boolean $createManifest
     *
     * @return $this
     */
    public function setCreateManifest( $createManifest )
    {
        $this->_createManifest = $createManifest;

        return $this;
    }

    public function showManifest()
    {
        return $this->_showManifest;
    }

    /**
     * @param boolean $showManifest
     *
     * @return $this
     */
    public function setShowManifest( $showManifest )
    {
        $this->_showManifest = $showManifest;

        return $this;
    }

    /**
     * @return boolean
     */
    public function noKeys()
    {
        return $this->_noKeys;
    }

    /**
     * @param bool $noKeys
     *
     * @return $this
     */
    public function setNoKeys( $noKeys = false )
    {
        $this->_noKeys = $noKeys;

        return $this;
    }

    /**
     * @return string
     */
    public function getHandler()
    {
        return 'DreamFactory\\Enterprise\\Services\\Handlers\\Commands\\ManifestHandler';
    }
}
