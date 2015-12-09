<?php
//******************************************************************************
//* Console command general settings
//******************************************************************************
return [
    //******************************************************************************
    //* Information displayed when running commands
    //******************************************************************************
    'display-name'      => 'DreamFactory Enterprise(tm) Console',
    'display-version'   => 'v1.0.6',
    'display-copyright' => 'Copyright (c) 2012-' . date('Y') . ', All Rights Reserved',
    //******************************************************************************
    //* Individual command settings
    //******************************************************************************
    /** dfe:setup */
    'setup'             => [
        /** Necessary directory structure and modes */
        'required-directories' => [
            'bootstrap/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
        ],
    ],
];
