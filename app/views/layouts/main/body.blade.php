<div id="wrapper">
    @include('layouts.main.navbar')

    <div id="page-wrapper" class="container-fluid">
        @include('app.breadcrumbs', array( '_trail' => array( 'Dashboard' => false), '_buttons'=>false ))

        <div class="row page-content nano">
            <div class="col-md-12 nano-content">
                @yield('content')
            </div>
        </div>
    </div>
</div>
