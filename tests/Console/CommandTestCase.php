<?php namespace DreamFactory\Enterprise\Console\Tests\Console;

abstract class CommandTestCase extends \TestCase
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string The command name
     */
    protected $command = 'dfe:mount';
    /**
     * @type array Command arguments
     */
    protected $arguments = [];
    /**
     * @type string Command options
     */
    protected $options = [];

    //******************************************************************************
    //* Members
    //******************************************************************************
}