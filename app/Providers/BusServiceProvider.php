<?php namespace DreamFactory\Enterprise\Console\Providers;

use Illuminate\Bus\Dispatcher;
use Illuminate\Support\ServiceProvider;

class BusServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap any application services.
     *
     * @param  \Illuminate\Bus\Dispatcher $dispatcher
     *
     * @return void
     */
    public function boot( Dispatcher $dispatcher )
    {
        //  The namespaces from which we use commands
        static $_mappings = [
            'DreamFactory\\Enterprise\\Console\\Console\\Commands' => 'DreamFactory\\Enterprise\\Console\\Handlers\\Commands',
            'DreamFactory\\Enterprise\\Services\\Commands'         => 'DreamFactory\\Enterprise\\Services\\Handlers\\Commands'
        ];

        $dispatcher->mapUsing(
            function ( $command ) use ( $_mappings )
            {
                $_class = get_class( $command );
                $_classNamespace = trim( substr( $_class, 0, strrpos( $_class, '\\' ) ), '\\' );
                $_cleaned = trim( str_replace( $_classNamespace, null, $_class ), '\\' );

                foreach ( $_mappings as $_commandSpace => $_handlerSpace )
                {
                    $_handler = $_handlerSpace . '\\' . $_cleaned . 'Handler';

                    if ( $_classNamespace == $_commandSpace && class_exists( $_handler ) )
                    {
                        return $_handler . '@handle';
                    }
                }

                throw new \RuntimeException( 'The handler for class "' . get_class( $command ) . '" cannot be found.' );
            }
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}
