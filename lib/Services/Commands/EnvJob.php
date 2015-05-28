<?php namespace DreamFactory\Enterprise\Services\Commands;

use DreamFactory\Enterprise\Common\Commands\EnterpriseJobCommand;

class EnvJob extends EnterpriseJobCommand
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The absolute path to the output file
     */
    protected $_outputFile;

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
     * @return EnvJob
     */
    public function setOutputFile( $outputFile )
    {
        $this->_outputFile = $outputFile;

        return $this;
    }

}
