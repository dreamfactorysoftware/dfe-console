<?php

$_active = DashboardController::getActiveCounts();
?>
<div class="row breadcrumb-content">
    <div class="col-lg-9 no-padding-left">
        {{ renderBreadcrumbs(array(Route::currentRouteName()=>false,false),$_buttons) }}
    </div>

    <div class="col-lg-3 dashboard-header-info">
        <div class="pull-right">
            <div>{{ $_active['clusters'] }} clusters</div>
            <div>{{ $_active['instances'] }} instances</div>
            <div>{{ $_active['users'] }} users</div>
        </div>
    </div>
</div>
