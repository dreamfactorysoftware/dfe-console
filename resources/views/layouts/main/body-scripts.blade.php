<!-- Bootstrap Core JavaScript -->
<script src="/static/bootstrap-3.3.2/js/bootstrap.min.js"></script>
<script src="//cdn.datatables.net/1.10.4/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/plug-ins/3cfcc339e89/integration/bootstrap/3/dataTables.bootstrap.js"></script>
<script src="/static/highcharts/4.0.4/highcharts.min.js"></script>
<script src="/js/chart-theme.js"></script>
<!-- Material Design for Bootstrap -->
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.2.2/js/material.min.js"></script>
<script>
jQuery(function($) {
    //	Enable MD effects on doc-ready
    $.material.init();
});
</script>
<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.2.2/js/ripples.min.js"></script>
<script src="/js/EnterpriseServer.js"></script>
<script src="/js/cerberus.js"></script>
<script src="/js/cerberus.graphs.js"></script>
{{--<script src="/js/nanoscroller.min.js" defer="defer"></script>--}}
@section('body-scripts')
@show