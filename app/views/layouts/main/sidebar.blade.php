<?php
$_uri = URL::getRequest()->getRequestUri();
?>
<div class="collapse navbar-collapse navbar-collapse-sidebar">
    <ul class="nav nav-pills nav-stacked side-nav">
        <li role="presentation" {{ '/' == $_uri ? ' class="active"' : null }}>
            <a href="/"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
        </li>
        <li role="presentation">
            <a data-toggle="collapse" data-target="#menu-settings"><i class="fa fa-cogs fa-fw"></i> Configuration <i class="fa fa-caret-down fa-fw"></i></a>

            <ul id="menu-settings" class="nav nav-pills nav-stacked collapse in">
                <li role="presentation" {{ '/settings/roles' == $_uri ? ' class="active"' : null }}>
                    <a href="/settings/roles"><i class="fa fa-users fa-fw"></i> Roles & Limits</a>
                </li>
                <li role="presentation" {{ '/settings/servers' == $_uri ? ' class="active"' : null }}>
                    <a href="/settings/servers"><i class="fa fa-desktop fa-fw"></i> Servers</a>
                </li>
                <li role="presentation" {{ '/settings/clusters' == $_uri ? ' class="active"' : null }}>
                    <a href="/settings/clusters"><i class="fa fa-sitemap fa-fw"></i> Clusters</a>
                </li>
                <li role="presentation" {{ '/settings/instances' == $_uri ? ' class="active"' : null }}>
                    <a href="/settings/instances"><i class="fa fa-desktop fa-fw"></i> Instances</a>
                </li>
                <li role="presentation" {{ '/settings/users' == $_uri ? ' class="active"' : null }}>
                    <a href="/settings/users"><i class="fa fa-user fa-fw"></i> Users</a>
                </li>
            </ul>
        </li>
        <li role="presentation" {{ '/app/reports' == $_uri ? ' class="active"' : null }}>
            <a href="/app/reports"><i class="fa fa-th-list fa-fw"></i> Reports</a>
        </li>
        <li role="presentation" {{ '/app/support' == $_uri ? ' class="active"' : null }}>
            <a href="/app/support"><i class="fa fa-info-circle fa-fw"></i> Support</a>
        </li>
    </ul>
</div>
<!-- /.navbar-collapse -->
