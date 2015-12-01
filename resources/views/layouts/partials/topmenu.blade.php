<?php
use DreamFactory\Enterprise\Console\Enums\ConsoleDefaults;
use Illuminate\Support\Str;

//  Set prefix if missing
$prefix = isset($prefix) ? $prefix : ConsoleDefaults::UI_PREFIX;

//  And page name (derived from route)
if (!isset($pageName) || empty($pageName)) {
    $_parts = explode('.', Route::getCurrentRoute()->getName());
    $pageName = isset($_parts, $_parts[sizeof($_parts) - 2]) ? Str::title($_parts[sizeof($_parts) - 2]) : null;
}

$_linkPrefix = '/' . ConsoleDefaults::UI_PREFIX . '/';
$_resources = ['Home', 'Servers', 'Clusters', 'Users', 'Instances', 'Limits'];

if (!empty(config('reports.connections.' . config('reports.default') . '.client-host'))) {
    $_resources[] = 'Reports';
}
?>
<div class="dfe-topmenu">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div style="vertical-align: top">
                    <h1 class="text-primary pull-left dfe-topmenu-pagename">{{ $pageName }}</h1>
                    <ul class="nav nav-pills pull-right visible-md visible-lg">
                        @foreach ($_resources as $_resource)
                            <li class="{{ $_resource == $pageName ? 'active' : null }}">
                                <a href="{{ $_linkPrefix . strtolower($_resource) }}">{{ $_resource }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
