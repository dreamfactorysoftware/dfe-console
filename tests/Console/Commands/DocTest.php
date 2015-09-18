<?php namespace DreamFactory\Enterprise\Console\Tests\Console\Commands;

class DocTest extends \TestCase
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Tests doc request
     */
    public function testDoc()
    {
        \Artisan::call('dfe:db-doc', ['--format' => 'mediawiki_table', 'table' => 'vendor_t']);
    }
}