<?php
if ( !isset( $_active ) )
{
    $_active = DashboardController::getActiveCounts();
}
?>
<div class="breadcrumb-wrapper">
    <div class="row">
        <div class="col-md-6 breadcrumb-title">
            <i class="fa fa-fw fa-bars breadcrumb-menu-icon"></i> @yield('breadcrumb-title')
        </div>

        <div class="col-md-6 breadcrumb-activity">
            <div class="pull-right">
                <div>
                    <i class="fa fa-fw fa-sitemap"></i> <span id="breadcrumb-activity-clusters">@yield('breadcrumb-cluster-count', $_active['clusters'])</span>
                </div>
                <div><i class="fa fa-fw fa-desktop"></i>
                    <span id="breadcrumb-activity-provisioned">@yield('breadcrumb-instance-count', $_active['instances'])</span>
                </div>
                <div><i class="fa fa-fw fa-user"></i> <span id="breadcrumb-activity-users">@yield('breadcrumb-user-count', $_active['users'])</span>
                </div>
            </div>
        </div>
    </div>
</div>
