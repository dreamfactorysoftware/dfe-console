<?php
return [
    //  View Storage Paths
    'paths'    => [
        realpath( base_path( 'resources/views' ) )
    ],
    //  Compiled View Path
    'compiled' => storage_path() . '/framework/views',
];
