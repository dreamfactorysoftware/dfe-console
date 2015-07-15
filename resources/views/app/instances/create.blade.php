@include('layouts.partials.topmenu',array('pageName' => 'Instances', 'prefix' => $prefix))
@extends('layouts.main')
@section('content')
    <script type='text/javascript'>

        $( document ).ready(function() {
            //$('#')

        });



        function save(){
            var  formData = {
                instance_name_text:         $('#instance_name_text').val(),
                instance_cluster_select:    $('#instance_cluster_select').children(":selected").attr('id'),
                instance_policy_select:     $('#instance_policy_select').children(":selected").attr('id'),
                instance_ownername_text:    $('#instance_ownername_text').val()
            };

            $.ajax({
                url : "/{{$prefix}}/instances",
                type: "POST",
                data : formData,
                success: function(data, textStatus, jqXHR)
                {
                    //window.location = '/{{$prefix}}/instances';
                    console.log(data);
                },
                error: function (jqXHR, textStatus, errorThrown)
                {
                    console.log(textStatus);
                }
            });

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
                                            <li class="">
                                                <a class="" href="/{{$prefix}}/instances">Manage</a>
                                            </li>
                                            <li class="active">
                                                <a class="" href="/{{$prefix}}/instances/create">Create</a>
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
                                            <df-section-header class="" data-title="'Create Instance'">
                                                <div class="df-section-header df-section-all-round">
                                                    <h4 class="">Create Instance</h4>
                                                </div>
                                            </df-section-header>

                                            <form class="" name="create-user">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Name</label>
                                                            <input id="instance_name_text" class="form-control" placeholder="Enter instance name." type="name">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Cluster</label>
                                                            <select class="form-control" id="instance_cluster_select">
                                                                <option value="" disabled selected>Select Cluster</option>
                                                                @foreach ($clusters as $cluster)
                                                                    <option id="{{$cluster['id']}}">{{$cluster['cluster_id_text']}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Policy</label>
                                                            <select class="form-control" id="instance_policy_select">
                                                                <option value="" disabled selected>Select Policy</option>
                                                            </select>
                                                        </div>



                                                        <div class="form-group">
                                                            <label>Owner</label>
                                                            <input id="instance_ownername_text" class="form-control" value="{{ Auth::user()->email_addr_text }}" type="owner" disabled>
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

                                                                <button type="button" class="btn btn-primary" onclick="javascript:save();">
                                                                    Create
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



@stop


