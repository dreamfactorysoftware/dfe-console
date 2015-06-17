<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Our base application controller
 *
 * @package DreamFactory\Enterprise\Console\Http\Controllers
 */
abstract class Controller extends BaseController
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use DispatchesCommands, ValidatesRequests;

}
