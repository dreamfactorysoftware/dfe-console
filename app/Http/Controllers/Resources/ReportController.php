<?php
namespace DreamFactory\Enterprise\Console\Http\Controllers\Resources;

use Illuminate\Support\Facades\View;

//use Illuminate\Html\HtmlServiceProvider;

class ReportController extends ResourceController
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /** @type string */
    protected $_tableName = 'report_t';
    /** @type string */
    protected $_model = 'DreamFactory\\Enterprise\\Database\\Models\\Report';
    /** @type string */
    protected $_resource = 'report';

    protected $_prefix = 'v1';

    public function index()
    {
        $clusters = ['1', 'cluster', 'east'];
        $clusters = json_encode($clusters);

        $endpoints = [
            '&#47;rest&#47;db&#47;MichiganSetpoints',
            '/rest/db/MichiganModelOverride',
            '/rest/db/MichiganAve',
            '/rest/user/session',
            '/rest/db/joke',
            '/rest/system/config',
            '//rest/user/session',
            '/rest/api_docs',
            '/rest/api_docs/email',
            '/rest/api_docs/user',
        ];
        $endpoints = json_encode($endpoints);

        $roles = ['engine', 'jro', 'role', 'my', 'test', 'josh'];
        $roles = json_encode($roles);

        $instance_ids = ['1', 'east', 'web', 'next.cloud.dreamfactory.com', 'macbook'];
        $instance_ids = json_encode($instance_ids);

        $applications = [
            'orm',
            'admin',
            'todojquery',
            'dfauth',
            'todoangular',
            'testapp',
            'pollutants_visualization',
            'sensobee',
            'midata',
            'bibo',
        ];
        $applications = json_encode($applications);

        $users = ['gmail.com', 'zsoldoszsolt', 'dreamfactory.com', 'jerryablan', 'dmartin'];
        $users = json_encode($users);

        return View::make('app.reports')->with('prefix', $this->_prefix)->with('clusters', $clusters)->with('endpoints',
                $endpoints)->with('roles', $roles)->with('instance_ids', $instance_ids)->with('applications',
                $applications)->with('users', $users);//.index');//->with('nerd', $test);
    }

    public function show($id)
    {
        if ($id == 'bandwidth') {

            $applications = [
                'admin',
                'simsa',
                'orm',
                'af1',
                'osa',
                'gno_app',
                'inv',
                'jigarpatel',
                'todoangular',
                'sensobee',
            ];
            $applications = json_encode($applications);

            $clusters = ['1', 'cluster', 'east'];
            $clusters = json_encode($clusters);

            $endpoints = [
                '/rest/files/applications/calendar/',
                '/rest/files/applications/',
                '/rest/bi-storage/jobAds',
                '/rest/system/service/6',
                '/rest/db/MichiganAve',
                '/rest/medb/medicos',
                '/rest/medb/_schema',
                '/rest/af1/PX5',
                '/rest/files/applications/neboola/',
                '/rest/files/applications/neboola/lib/',
            ];
            $endpoints = json_encode($endpoints);

            $instance_ids = [
                '1',
                'east',
                'web',
                'next.cloud.dreamfactory.com',
                'macbook',
                'pro.local',
                'df',
                'jablan',
                'ablan',
                'jerry',
                'test',
                'case',
            ];
            $instance_ids = json_encode($instance_ids);

            $users = ['gmail.com', 'zsoldoszsolt', 'dreamfactory.com', 'fh.org', 'dmartin'];
            $users = json_encode($users);

            $roles = ['engine', 'jro', 'role', 'my', 'test', 'josh'];
            $roles = json_encode($roles);
        }

        return View::make('app.reports.bandwidth')->with('prefix', $this->_prefix)->with('clusters',
                $clusters)->with('endpoints', $endpoints)->with('roles', $roles)->with('instance_ids',
                $instance_ids)->with('applications', $applications)->with('users', $users);
    }

}

?>