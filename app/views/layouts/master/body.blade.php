@section('master.body')
    <body>

    @include('master.content_header')

    <div id="main" class="container-fluid">

        <div class="row">
            @include('master.sidebar')
            @include('master.content')
        </div>

    </div>

    <div id="loading-overlay" style="display: none;">Loading...</div>

    @include('master.content_footer')
    </body>
@stop
