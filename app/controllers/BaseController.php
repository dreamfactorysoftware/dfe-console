<?php
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;

/**
 * Our base controller
 */
class BaseController extends Controller
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @type string Our layout name
     */
    protected $layout = 'layouts.master';

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if ( !is_null( $this->layout ) )
        {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->layout = View::make( $this->layout );
        }
    }

}
