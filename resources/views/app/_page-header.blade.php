<?php
use DreamFactory\Library\Utility\IfSet;
use DreamFactory\Library\Utility\Inflector;

$_html = null;
$idPrefix = isset( $idPrefix ) ? $idPrefix : 'header-bar-';
$pageName = isset( $pageName ) ? $pageName : 'Page';

if ( isset( $buttons ) && is_array( $buttons ) )
{
    $_type = rtrim( Inflector::neutralize( $pageName ), 's' );

    foreach ( $buttons as $_id => $_options )
    {
        $_html .=
                '<button data-type="' .
                $_type .
                '" class="btn ' .
                IfSet::get( $_options, 'color', 'btn-info' ) .
                ' btn-sm" id="' .
                $idPrefix .
                $_id .
                '">';

        if ( null !== ( $_icon = IfSet::get( $_options, 'icon' ) ) )
        {
            $_html .= '<i class="fa fa-fw fa-' . $_icon . '"></i>';
        }

        $_html .= '</button>';
    }

    unset( $_id, $_options );

    if ( $_html )
    {
        $_html = '<div class="page-header-toolbar pull-right">' . $_html . '</div>';
    }
}
?>
<div class="row">
    <div class="col-md-12">
        <div class="pull-left"><h3 class="page-header">{{ $pageName }}</h3></div>
        {!! $_html !!}
        <div class="hr"></div>
    </div>
</div>