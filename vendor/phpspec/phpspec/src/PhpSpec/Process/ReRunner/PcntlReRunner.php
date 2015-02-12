<?php

/*
 * This file is part of PhpSpec, A php toolset to drive emergent
 * design by specification.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpSpec\Process\ReRunner;

class PcntlReRunner extends PhpExecutableReRunner
{
    /**
     * @return bool
     */
    public function isSupported()
    {
        return (php_sapi_name() == 'cli')
            && $this->getExecutablePath()
<<<<<<< HEAD
            && function_exists('pcntl_exec')
            && !defined('HHVM_VERSION');
=======
            && function_exists('pcntl_exec');
>>>>>>> 72fb08a0172f98796ac5af1b91ec18f1c5421cc4
    }

    /**
     * Kills the current process and starts a new one
     */
    public function reRunSuite()
    {
        $args = $_SERVER['argv'];
        pcntl_exec($this->getExecutablePath(), $args);
    }
}
