<?php
//******************************************************************************
//* Configuration file for the dfe:setup command
//******************************************************************************/**
return [
    'display-name'         => 'DreamFactory Enterprise Setup and Initialization',
    'display-version'      => 'v1.0.x-alpha',
    'display-copyright'    => 'Copyright (c) 2012-' . date( 'Y' ) . ', All Rights Reserved',
    /** Necessary directory structure and modes */
    'required-directories' => [
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
    ],
];
