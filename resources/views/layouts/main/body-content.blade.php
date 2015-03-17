@section('body-content')
    @include('layouts.main.navbar')

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-3 col-md-2 sidebar">
                @include('layouts.main.sidebar')
            </div>

            <div id="content" class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                @yield('content')
            </div>
        </div>
    </div>

    <div id="loading-overlay" style="display: none;">Loading...</div>
    <div class="loading-content" style="display: none;"><img src="/img/img-loading.gif" class="loading-image" alt="Loading..." /></div>
@stop
