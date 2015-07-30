<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Library\Fabric\Database\Models\Deploy;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Library\Utility\Curl;
use DreamFactory\Enterprise\Database\Models\Instance;

use Illuminate\Support\Facades\View;
use Illuminate\Routing\Controller;


class LimitController extends Controller //ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'limit_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Limit';
    /** @type string */
    protected $_resource = 'policy';

    protected $_prefix = 'v1';

    //******************************************************************************
    //* Traits
    //******************************************************************************

    use EntityLookup;


    public function getInstances($cluster_id)
    {

        return Instance::where('cluster_id', '=', $cluster_id)
            ->orderBy('instance_t.instance_name_text')
            ->get(['id', 'instance_name_text']);

    }

    public function getServices($instance_id)
    {
        $_instance = Instance::ByNameOrId($instance_id);

        $_url = $_instance->buildInstanceUrl();


    }

}

?>