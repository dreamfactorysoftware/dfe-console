@include('layouts.partials.topmenu',array('pageName' => 'Clusters', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')

    <script type='text/javascript'>

    $(document).ready(function() {
        //$('#')

    });

    /*
     function save(){
     var  formData = {

     cluster_name_text: $('#cluster_name_text').val(),
     cluster_subdomain_text: $('#cluster_subdomain_text').val(),
     cluster_instancecount_text: $('#cluster_instancecount_text').val()


     };
     console.log(formData);

     $.ajax({
     url : "/{{$prefix}}/clusters",
     type: "POST",
     data : formData,
     success: function(data, textStatus, jqXHR)
     {
     //data - response from server
     window.location = '/{{$prefix}}/clusters';
     },
     error: function (jqXHR, textStatus, errorThrown)
     {
     console.log(textStatus);
     }
     });

     }
     */
    /*
     // $(document).ready(function() {
     $("#system_admin").click(function () {
     if ($('#system_admin').is(':checked')) {
     $('#instance_manage').removeAttr("disabled");
     $('#instance_policy').removeAttr("disabled");

     } else {
     $('#instance_manage').attr("disabled", true);
     $('#instance_policy').attr("disabled", true);
     }
     });

     $('#set_password').click(function () {
     if ($('#set_password').is(':checked')) {
     $('#set_password_form').show();

     } else {
     $('#set_password_form').hide();

     }
     });
     // });
     */
    </script>

    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="row">
                    <div class="ng-scope">
                        <div class="ng-scope">
                            <div class="col-md-2 df-sidebar-nav">
                                <df-sidebar-nav>
                                    <div class="">
                                        <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                                            <li class="">
                                                <a class="" href="/{{$prefix}}/clusters">Manage</a>
                                            </li>
                                            <li class="active">
                                                <a class="" href="/{{$prefix}}/clusters/create">Create</a>
                                            </li>
                                        </ul>
                                        <div class="hidden-lg hidden-md" id="sidebar-open">
                                            <button type="button" class="btn btn-default btn-sm"><i class="fa fa-fw fa-bars"></i></button>
                                        </div>

                                    </div>
                                </df-sidebar-nav>
                            </div>
                            <div class="col-md-10 df-section df-section-3-round" df-fs-height="">
                                <df-manage-users class="">
                                    <div>
                                        <div class="">
                                            <df-section-header class="" data-title="'Manage Servers'">
                                                <div class="df-section-header df-section-all-round">
                                                    <h4 class="">Create Cluster</h4>
                                                </div>
                                            </df-section-header>

                                            <form class="" name="create-user" method="POST" action="/{{$prefix}}/clusters">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Name</label>
                                                            <input id="cluster_id_text"
                                                                name="cluster_id_text"
                                                                class="form-control"
                                                                placeholder="cluster-[zone]-[#]"
                                                                type="text">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>DNS Subdomain</label>
                                                            <input id="subdomain_text"
                                                                name="subdomain_text"
                                                                class="form-control"
                                                                placeholder=".pasture.farm.com"
                                                                type="text">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Maximum Allowed Instances</label>
                                                            <input class="form-control"
                                                                   name="max_instances_nbr"
                                                                   type="number"
                                                                   min="0"
                                                                   max="1000"
                                                                   value="0">
                                                        </div>

                                                    </div>

                                                    <div class="col-md-6">

                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-xs-12">
                                                        <hr>
                                                        <div class="form-group">
                                                            <div class="">
                                                                <button type="submit" class="btn btn-primary">Create</button>
                                                                <!--button type="button" class="btn btn-primary" onclick="javascript:save();">
                                                                    Create
                                                                </button-->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        </df-user-details>
                                    </div>

                                </df-manage-users>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



@stop


