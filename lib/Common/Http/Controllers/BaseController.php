<?php namespace DreamFactory\Enterprise\Common\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

/**
 * The base DFE controller
 */
abstract class BaseController extends Controller
{
    //******************************************************************************
    //* Traits
    //******************************************************************************

    use DispatchesCommands, ValidatesRequests;
}
