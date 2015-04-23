<?php
namespace Usr\Local\Bin;

class RemoveRemote
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string
     */
    protected $_scriptName;
    /**
     * @type array
     */
    protected $_args = [];
    /**
     * @type int
     */
    protected $_argCount = 0;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param int   $argc
     * @param array $argv
     */
    public function __construct( $argc = 0, $argv = [] )
    {
        $this->_scriptName = basename( $argv[0] );

        if ( empty( $argc ) || empty( $argv ) )
        {
            $this->_usage( 'No arguments.' );
        }

        $this->_argCount = $argc;
        $this->_args = $argv;
    }

    /**
     * runs the command
     */
    public function run()
    {
        for ( $_i = 1; $_i < $this->_argCount; $_i++ )
        {
            $_argument = $this->_args[$_i];
            $_path = shell_exec( '$(builtin printf "%q " "~/dl/' . $_argument . '")' );

            $_command = <<<SHELL
ssh tff rm -rf {$_path}
SHELL;

            //$(printf '%q ' mv -v "/home/dan/Downloads/complete/$dir" /home/dan/Downloads/downloaded)

            //exec( $_command, $_output, $_return );
            //$this->_echo( ( 0 != $_return ? 'fail' : 'win :' ) . $_argument );

            echo $_command . PHP_EOL;
        }
    }

    /**
     * @param string $message
     */
    protected function _echo( $message )
    {
        echo basename( $this->_args[0] ) . ': ' . $message;
    }

    /**
     * @param string $errorMessage
     * @param int    $exitCode
     */
    protected function _usage( $errorMessage = null, $exitCode = 1 )
    {
        global $argv;

        $errorMessage && $this->_echo( $errorMessage );

        echo <<< USAGE
usage: {$this->_scriptName} [[glob]...]
USAGE;

        exit( $exitCode );
    }
}

/*#!/bin/bash
echo "${1}"
ssh tff "cd ~/dl; find . -name \"$1\" -print0 | xargs -0 rm -rf"
cd ~/dl; find . -maxdepth 1 - name "$1" - print0 | xargs - 0 rm - rf; cd - >/dev / null*/

/**
 *  usage() {
 * local _arg="${1}"
 *
 * [ "${1}" = "" -o "${1}" = "0" ] && _arg=0
 *
 * _msg "usage" ${_YELLOW} "${_ME} [--push|--pop] [<library|app>|[on|off] [on|off] [[-q][-h|--help]]]"
 *
 * echo
 * echo "  Where:"
 * echo
 * echo "  ${B1}library${B2}        is one of the following: "
 * echo
 * echo "                    ${B1}platform${B2}, ${B1}yii${B2}, ${B1}yiif${B2}, ${B1}common${B2},"
 * echo "                    ${B1}oasys${B2}, ${B1}console-tools|ctools${B2}, ${B1}kisma${B2}, ${B1}fabric${B2}, "
 * echo "                    ${B1}plugin-mounty|mounty${B2}, or ${B1}php-utils|putils|utils${B2}"
 * echo
 * echo "                 The proprietary ${B1}Enterprise${B2} libraries are available as well:"
 * echo
 * echo "                    ${B1}fabric-common|fcommon${B2}, ${B1}fabric-db|fdb${B2}, ${B1}fabric-couchdb|fcouchdb${B2},"
 * echo "                    ${B1}fabric-queue-models|fmodels${B2}, ${B1}fabric-queue|fqueue${B2}, ${B1}dfe-audit${B2}, ${B1}dfe-common${B2},
 * ${B1}dfe-console${B2}," echo "                    and ${B1}dfe-ops-client${B2}" echo echo "  ${B1}app${B2}            is any repo under
 * ${B1}${APP_SOURCE_PATH}${B2}." echo echo "  ${B1}on${B2}             turns  ${B1}ON${B2} all currently ${B1}unlinked${B2} vendor libraries." echo
 * "  ${B1}off${B2}            turns ${B1}OFF${B2} all currently   ${B1}linked${B2} vendor libraries." echo echo "  ${B1}on${B2}|${B1}off${B2}
 *  is the operation you wish to perform. Defaults to '${B1}on${B2}'" echo echo "  ${B1}source_path${B2}    is where your source projects are for
 *  these libraries. " echo "                    Defaults to /path/to/repos/${B1}type${B2}" echo echo "  ${B1}-q${B2}             enables quiet mode.
 *  No errors are output if set. Used by global on/off switch." echo "  ${B1}-h${B2}             shows this help information." echo "
 *  ${B1}--help${B2}         shows the extended help information." echo "  ${B1}--push${B2}         pushes the current links for later popping." echo
 *  "  ${B1}--pop${B2}          pops any previously pushed links and restores them." echo
 *
 * sectionHeader " Examples "
 * echo "  $ sdv on                # Turn on all libraries"
 * echo "  $ sdv off               # Turn off all libraries"
 * echo "  $ sdv platform on           # Turn on platform library"
 */

/**
 * Create the command and run it...
 */
global $argc, $argv;

$_command = new RemoveRemote( $argc, $argv );
$_command->run();