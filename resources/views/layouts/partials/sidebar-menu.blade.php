<?php
/**
 * @var string $prefix
 * @var string $resource
 */
$_uri = URL::getRequest()->getRequestUri();
$_base = '/' . $prefix . '/' . $resource;
$_active = ' class="active"';
?>
<div class="col-xs-1 col-sm-2 col-md-2 df-sidebar-nav">
	<ul class="nav nav-pills nav-stacked visible-md visible-lg">
		<li @if($_uri == $_base){!! $_active !!}@endif>
			<a href="{{ $_base }}">Manage</a>
		</li>
		<li @if($_uri == ($_base . '/create')){!! $_active !!}@endif>
			<a href="{{ $_base . '/create' }}">Create</a>
		</li>
	</ul>
</div>
