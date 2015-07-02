<?php namespace DreamFactory\Enterprise\Console\Tests\General;

use DreamFactory\Enterprise\Database\Models\Cluster;

class ModelsTest extends \TestCase
{
    public function testOne()
    {
        /** @type Cluster $_cluster */
        $_cluster = Cluster::byNameOrId('cluster-east-1')->firstOrFail();
        $_assigns = $_cluster->assignedServers();

        foreach ($_assigns as $_assign) {
            if ($_assign->server) {
                $_server = $_assign->server;

                if ($_server->mount) {
                    $_mount = $_server->mount;
                }
            }
        }
    }
}
