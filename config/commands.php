<?php
//******************************************************************************
//* Console command general settings
//******************************************************************************
return [
    /** dfe:setup */
    'setup' => [
        /** Necessary directory structure and modes */
        'required-directories' => [
            'bootstrap/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
        ],
    ],
];
