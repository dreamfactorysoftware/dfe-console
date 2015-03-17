<?php
$_html = null;

if ( !empty( $messages ) )
{
    $_html = '<div class="alert alert-error alert-fixed fade in" data-alert="alert"><strong>Please check your entries.</strong>';

    foreach ( $messages->all( '<p>:message</p>' ) as $_errorMessage )
    {
        $_html .= $_errorMessage;
    }

    $_html .= '</div>';
}
?>
@extends('layouts.login')

@section('page-title')
    Login
@overwrite
