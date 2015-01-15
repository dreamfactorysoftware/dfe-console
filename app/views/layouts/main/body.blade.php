<div id="wrapper">
    @include('layouts.main.navbar')

    <div id="page-wrapper" class="container-fluid">
        @include('app.breadcrumbs', array( '_trail' => array( 'Dashboard' => false), '_buttons'=>false ))

        <div class="row">
            <div class="col-lg-12">
                @yield('content')
            </div>
        </div>
    </div>
</div>
