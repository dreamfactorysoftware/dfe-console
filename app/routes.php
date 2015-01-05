<?php
Route::get(
    '/',
    function ()
    {
        return View::make( 'hello' );
    }
);

Route::get(
    'users',
    function ()
    {
        return View::make( 'users' );
    }
);
