<?php
/**
 * Configuration file for the dfe-common library
 */

return [
    /** Global options */
    'display-name'    => 'Admin Console',
    'display-version' => 'v1.0.x-alpha',
    /**
     * Theme selection -- a bootswatch theme name
     * Included are cerulean, darkly, flatly, paper, and superhero.
     * You may also install other compatible themes and use them as well.
     */
    'theme'           => 'flatly',
    'log-path'        => env( 'DFE_LOG_PATH', '/data/logs/console' ),
    'log-file-name'   => 'laravel.log',
];