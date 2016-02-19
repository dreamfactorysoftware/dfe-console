<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Services\Utility\FastTrack;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FastTrackController extends FactoryController
{
    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * @return View
     */
    public function index()
    {
        return view('fast-track.main');
    }

    /**
     * Receives a post and performs an auto-registration
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function autoRegister(Request $request)
    {
        try {
            $_response = FastTrack::register($request);
        } catch (HttpException $_ex) {
            return ErrorPacket::create(null, $_ex->getCode(), $_ex->getMessage());
        }

        //  Redirect with auto-login to new instance
        return SuccessPacket::create($_response);
    }
}
