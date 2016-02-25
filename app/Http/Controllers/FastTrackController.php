<?php namespace DreamFactory\Enterprise\Console\Http\Controllers;

use DreamFactory\Enterprise\Common\Packets\ErrorPacket;
use DreamFactory\Enterprise\Common\Packets\SuccessPacket;
use DreamFactory\Enterprise\Services\Utility\FastTrack;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
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
        return view('fast-track.main',
            [
                'launchButtonText' => \Lang::get('fast-track.launch-button-text'),
                'endpoint'         => config('dfe.fast-track-route'),
            ]);
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

            //  Redirect's get returned verbatim
            if ($request->input('redirect', true) && $_response instanceof RedirectResponse) {
                return $_response;
            }
        } catch (HttpException $_ex) {
            return ErrorPacket::create(null, $_ex->getCode(), $_ex->getMessage());
        }

        //  Send back the response...
        return SuccessPacket::create($_response);
    }
}
