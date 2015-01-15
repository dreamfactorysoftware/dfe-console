<?php
//******************************************************************************
//* Blade Extensions
//******************************************************************************

use Illuminate\Support\Facades\Blade;
use Illuminate\View\Compilers\BladeCompiler;

/** @noinspection PhpUndefinedMethodInspection */
Blade::extend(
    function ( $view, $compiler )
    {
        /** @type BladeCompiler $compiler */
        $_pattern = $compiler->createMatcher( 'render' );

        return preg_replace( $_pattern, '$1<?php echo renderBreadcrumbs$2; ?>', $view );
    }
);
