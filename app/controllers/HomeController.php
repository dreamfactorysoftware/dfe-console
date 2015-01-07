<?php
use Illuminate\Support\Facades\View;

/**
 * Front welcome
 */
class HomeController extends BaseController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return mixed
     */
    public function showWelcome()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return View::make( 'users' );
    }

}
