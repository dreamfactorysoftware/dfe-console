<div id="wrapper">
    @include('layouts.main.navbar')

    <div id="page-wrapper" class="container-fluid">
        @include('app.breadcrumbs', array( '_trail' => array( 'Dashboard' => false), '_buttons'=>false ))

        <div class="page-content nano">
            <div class="nano-content">
                @yield('content')
            </div>
        </div>
    </div>
</div>
