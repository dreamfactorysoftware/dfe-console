<?php namespace DreamFactory\Enterprise\Console\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * @var array A list of the exception types that should not be reported.
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException',
    ];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $ex
     */
    public function report(\Exception $ex)
    {
        parent::report($ex);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $ex
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, \Exception $ex)
    {
        return ($ex instanceof HttpException) ? $this->renderHttpException($ex) : parent::render($request, $ex);
    }
}
