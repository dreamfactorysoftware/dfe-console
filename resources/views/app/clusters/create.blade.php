@include('layouts.partials.topmenu',array('pageName' => 'Clusters', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')

    <script type='text/javascript'>

    $(document).ready(function() {
        //$('#')

    });

    </script>

    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="row">
                    <div>
                        <div>
                            <div class="col-md-2">
                                <div>
                                    <div>
                                        <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                                            <li>
                                                <a href="/{{$prefix}}/clusters">Manage</a>
                                            </li>
                                            <li class="active">
                                                <a href="/{{$prefix}}/clusters/create">Create</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div>
                                    <div>
                                        <div>
                                            <div class="nav nav-pills dfe-section-header">
                                                <h4>Create Cluster</h4>
                                            </div>
                                        </div>

                                        <form name="create-user" method="POST" action="/{{$prefix}}/clusters">
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
                                                        <div>
                                                            <button type="submit" class="btn btn-primary">Create</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



@stop


