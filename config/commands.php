<?php
//******************************************************************************
//* Console command general settings
//******************************************************************************
return [
    //******************************************************************************
    //* Information displayed when running commands
    //******************************************************************************
    'display-name'      => 'DreamFactory Enterprise(tm) Console Manager',
    'display-version'   => 'v1.0.x-alpha',
    'display-copyright' => 'Copyright (c) 2012-' . date('Y') . ', All Rights Reserved',
    //******************************************************************************
    //* Individual command settings
    //******************************************************************************
    /** dfe:setup */
    'setup'             => [
        /** Necessary directory structure and modes */
        'required-directories' => [
            'bootstrap/cache',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
        ],
    ],
];
