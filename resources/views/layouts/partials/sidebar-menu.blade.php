<?php
/**
 * @var string $prefix
 * @var string $resource
 */
$_uri = URL::getRequest()->getRequestUri();
$_base = '/' . $prefix . '/' . $resource;
$_active = ' class="active"';
$_items = ['Manage' => $_base, 'Create' => $_base . '/create'];
if ('instances' == $resource)
    $_items['Packages'] = $_base . '/packages';
?>
<div class="col-xs-1 col-sm-2 col-md-2 df-sidebar-nav">
    <ul class="nav nav-pills nav-stacked visible-md visible-lg">
        @foreach($_items as $_item => $_href)
            <li @if($_uri == $_href){!! $_active !!}@endif>
                <a href="{{ $_href }}">{{$_item}}</a>
            </li>
        @endforeach
    </ul>
</div>
