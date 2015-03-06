<?php namespace DreamFactory\Enterprise\Common\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

/**
 * Main exception handler
 *
 * @package DreamFactory\Enterprise\Common\Exceptions
 */
class Handler extends ExceptionHandler
{
    //******************************************************************************
    //* Members
    //******************************************************************************

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        'Symfony\Component\HttpKernel\Exception\HttpException'
    ];

    //******************************************************************************
    //* Methods
    //******************************************************************************

    /**
     * Report or log an exception.
     *
     * @param  \Exception $e
     *
     * @return void
     */
    public function report( Exception $e )
    {
        parent::report( $e );
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $e
     *
     * @return \Illuminate\Http\Response
     */
    public function render( $request, Exception $e )
    {
        if ( $this->isHttpException( $e ) )
        {
            return $this->renderHttpException( $e );
        }

        return parent::render( $request, $e );
    }

}
