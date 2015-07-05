<?php
//******************************************************************************
//* DFE Console views configuration
//******************************************************************************
return [
    //  View Storage Paths
    'paths'    => [
        realpath(base_path('resources/views')),
    ],
    //  Compiled View Path
    'compiled' => realpath(base_path('bootstrap/cache/views')),
];
