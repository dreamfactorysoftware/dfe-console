<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Ops;

use DreamFactory\Library\Fabric\Database\Models\Deploy;
use DreamFactory\Enterprise\Common\Traits\EntityLookup;
use DreamFactory\Enterprise\Database\Models\Instance;

use Illuminate\Support\Facades\View;
use Illuminate\Routing\Controller;


class PolicyController extends Controller //ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'policy_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Library\\Fabric\\Database\\Models\\Deploy\\Policy';
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

}

?>