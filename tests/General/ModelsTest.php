<?php namespace DreamFactory\Enterprise\Console\Tests\General;

use DreamFactory\Enterprise\Database\Models\Cluster;

class ModelsTest extends \TestCase
{
    public function testOne()
    {
        $_cluster = Cluster::byNameOrId('cluster-east-1')->firstOrFail();

        if ($_cluster->servers) {
            foreach ($_cluster->servers as $_server) {
                if ($_server->mount) {
                    $_mount = $_server->mount;
                }
            }
        }
    }
}
