<?php namespace DreamFactory\Enterprise\Services\Utility;

/**
 * Generates files of random sizes to fill up a subdirectory
 */
class TestFileGenerator
{
    //******************************************************************************
    //* Constants
    //******************************************************************************

    const MAX_ITERATIONS = 1000;
    const MAX_SIZE       = 1000;

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @param string $prefix
     * @param string $extension
     * @param int    $blockSize The block size to write. Function creates from 240-1000 x 1024b (240k-1mb files)
     *
     * @return int The number of files generated
     */
    public function generate($prefix = null, $extension = 'php', $blockSize = 512)
    {
        for ($_i = 0, $_count = rand(static::MAX_ITERATIONS / 4, static::MAX_ITERATIONS); $_i < $_count; $_i++) {
            $_file = $this->_getName($_i);
            $_size = rand(static::MAX_SIZE / 4, static::MAX_SIZE);

            $_result = `dd if=/dev/zero of={$_file} count={$_size} bs={$blockSize} status=none`;
        }

        return $_i;
    }

    /**
     * @param string $prefix
     * @param string $extension
     *
     * @return string
     */
    protected function _getName($prefix = null, $extension = 'php')
    {
        $extension = $extension ? '.' . trim($extension, ' .') : null;
        $prefix = $prefix ? trim($prefix, ' .') . '.' : null;

        return $prefix . sha1(microtime(true) . gethostname() . getmypid()) . $extension;
    }

}