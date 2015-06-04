<?php
/**
 * Configuration file for the dfe-common library
 */

return [
    /**
     * Settings for common auth (i.e. name, version, etc.)
     */
    'display-name'      => 'Admin Console',
    'display-version'   => 'v1.0.x-alpha',
    'display-copyright' => null,
    /**
     * Theme selection -- a bootswatch theme name
     * Included are cerulean, darkly, flatly, paper, and superhero.
     * You may also install other compatible themes and use them as well.
     */
    'theme'             => 'flatly',
    /** Log locations */
    'log-path'          => env( 'DFE_LOG_PATH', '/data/logs/console' ),
    'log-file-name'     => env( 'DFE_LOG_FILE_NAME', 'laravel.log' ),
];
