@include('layouts.partials.topmenu',['pageName' => 'Instances', 'prefix' => $prefix])
@extends('layouts.main')

@section('content')
    <script type='text/javascript'>

        $( document ).ready(function() {
            //$('#')
            var txt = "{{ $instance->cluster_id_text }}";
            $('#cluster_select')
                    .find('option')
                    .filter(function() { return $.trim( $(this).text() ) == txt; })
                    .attr('selected','selected');


        });

        function save(){
            var  formData = {
                /*
                user_id: '',
                email_addr_text: $('#email_addr_text').val(),
                first_name_text: $('#first_name_text').val(),
                last_name_text: $('#last_name_text').val(),
                system_admin: $('#system_admin').is(':checked'),
                active: $('#active').is(':checked'),
                instance_manage: $('#instance_manage').val(),
                instance_policy: $('#instance_policy').val(),
                set_password: $('#new_password').val()
                */
            };
            /*
            $.ajax({
                url : "/{{$prefix}}/servers",
                type: "PUT",
                data : formData,
                success: function(data, textStatus, jqXHR)
                {
                    //data - response from server
                },
                error: function (jqXHR, textStatus, errorThrown)
                {

                }
            });
            */
        }
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
                                            <li class="active">
                                                <a class="" href="/{{$prefix}}/instances">Manage</a>
                                            </li>
                                        </ul>
                                        <div class="hidden-lg hidden-md" id="sidebar-open">
                                            <button type="button" class="btn btn-default btn-sm"><i class="fa fa-fw fa-bars"></i></button>
                                        </div>

                                    </div>
                                </df-sidebar-nav>
                            </div>
                            <div class="col-md-10 df-section df-section-3-round" df-fs-height="">
                                <df-manage-users class=""><div>
                                        <div class="">
                                            <df-section-header class="" data-title="'Edit Instance'">
                                                <div class="nav nav-pills dfe-section-header">
                                                    <h4 class="">Create Cluster</h4>
                                                </div>
                                            </df-section-header>

                                            <form class="" name="create-user">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Name</label>
                                                            <input id="cluster_name_text" class="form-control" value="{{$instance->instance_id_text}}" type="name">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Cluster</label>
                                                            <select class="form-control" id="cluster_select">
                                                                <option value="" disabled selected>Select Cluster</option>
                                                                @foreach ($clusters as $cluster)
                                                                    <option id="{{$cluster['id']}}">{{$cluster['cluster_id_text']}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Policy</label>
                                                            <select class="form-control" id="server_type_select">
                                                                <option value="" disabled selected>Select Policy</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Owner</label>
                                                            <input id="owner_name_text" class="form-control" value="{{ $instance->email_addr_text }}" type="owner" disabled>
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
                                                                <button type="button" class="btn btn-default" onclick="cancelEditInstance();">
                                                                    Close
                                                                </button>
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

    <script type="text/javascript" src="/js/blade-scripts/instances/instances.js"></script>
@stop


