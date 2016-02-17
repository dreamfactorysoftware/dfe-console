<?php
//******************************************************************************
//* Console UI Icon Settings
//******************************************************************************
use DreamFactory\Enterprise\Common\Enums\ServerTypes;

return [
    'import'       => 'fa-undo',
    'upload'       => 'fa-cloud-upload',
    'export'       => 'fa-cloud-download',
    'spinner'      => 'fa fa-spinner fa-spin text-info',
    'up'           => 'fa-thumbs-o-up',
    'down'         => 'fa-thumbs-o-down',
    'starting'     => 'fa fa-spinner fa-spin text-success',
    'stopping'     => 'fa fa-spinner fa-spin text-warning',
    'terminating'  => 'fa fa-spinner fa-spin text-danger',
    'dead'         => 'fa-ambulance',
    'unknown'      => 'fa-question',
    'help'         => 'fa-question',
    'launch'       => 'fa-rocket',
    'create'       => 'fa-rocket',
    'start'        => 'fa-play',
    'stop'         => 'fa-stop',
    'server-types' => [
        ServerTypes::DB  => 'fa-database',
        ServerTypes::WEB => 'fa-dashboard',
        ServerTypes::APP => 'fa-server',
    ],
];
