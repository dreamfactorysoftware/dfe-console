<?php
return [
    //  View Storage Paths
    'paths'    => [
        realpath( base_path( 'resources/views' ) )
    ],
    //  Compiled View Path
    'compiled' => realpath( storage_path() . '/framework/views' ),
];
