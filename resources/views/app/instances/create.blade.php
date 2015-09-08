<<<<<<< HEAD
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
=======
@extends('layouts.main')
@include('layouts.partials.topmenu')
@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'instances'])

    <div class="col-xs-11 col-sm-10 col-md-10">
        @include('layouts.partials.context-header',['resource'=>'instances','title' => 'New Instance'])

    <form class="instance-form" method="POST" action="/{{$prefix}}/instances">
        <input name="_method" type="hidden" value="POST">
        <input name="_token" type="hidden" value="{{ csrf_token() }}">
        <input name="limit_period" id="limit_period" type="hidden" value="min">

>>>>>>> a40cfef5f74fe4313ac72520b694cdb2ebf9dbd7
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="cluster_id">Cluster</label>
                    <select class="form-control" id="cluster_id" name="cluster_id">
                        <option value="0">All Clusters</option>
                        @foreach ($clusters as $_cluster)
                            <option value="{{ $_cluster['cluster_id_text'] }}" {{ Input::old('cluster_id') == $_cluster['cluster_id_text'] ? 'selected="selected"' : null }}>{{ $_cluster['cluster_id_text'] }}</option>
                        @endforeach
                    </select>
                </div>



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
@stop
