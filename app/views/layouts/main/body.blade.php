<div id="wrapper">
    @include('layouts.main.navbar')

    <div id="page-wrapper">
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">@yield('page-header','Nada')
                        <small>@yield('page-subheader','me so empty!')</small>
                    </h1>

                    @breadcrumbs()
                </div>
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </div>
    <!-- /#page-wrapper -->
</div>
<!-- /#wrapper -->
