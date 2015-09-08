@include('layouts.partials.topmenu')
@extends('layouts.main')
@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'clusters'])

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
                            @if(Session::has('flash_message'))
                                <p class="alert {{ Session::get('flash_type') }}">{{ Session::get('flash_message') }}</p>
                            @endif
                            <div class="form-group">
                                <label>Name</label>
                                <input id="cluster_id_text"
                                    name="cluster_id_text"
                                    class="form-control"
                                    placeholder="Enter name."
                                    type="text"
                                    value="{{ Input::old('cluster_id_text') }}">
                            </div>
                            <div class="form-group">
                                <label>DNS Subdomain</label>
                                <input id="subdomain_text"
                                    name="subdomain_text"
                                    class="form-control"
                                    placeholder="Enter DNS Subdomain."
                                    type="text"
                                    value="{{ Input::old('subdomain_text') }}">
                            </div>
                            <div class="form-group">
                                <label>Select Web Server</label>
                                <select class="form-control" id="web_server_id" name="web_server_id">
                                    <option value="">Select web server.</option>
                                    @foreach ($web as $_web)
                                        <option value="{{ $_web['id'] }}" {{ Input::old('web_server_id') == $_web['id'] ? 'selected="selected"' : null }}>{{ $_web['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select Database Server</label>
                                <select class="form-control" id="db_server_id" name="db_server_id">
                                    <option value="">Select database server.</option>
                                    @foreach ($db as $_db)
                                        <option value="{{ $_db['id'] }}" {{ Input::old('db_server_id') == $_db['id'] ? 'selected="selected"' : null }}>{{ $_db['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select App Server</label>
                                <select class="form-control" id="app_server_id" name="app_server_id">
                                    <option value="">Select app server.</option>
                                    @foreach ($app as $_app)
                                        <option value="{{ $_app['id'] }}" {{ Input::old('app_server_id') == $_app['id'] ? 'selected="selected"' : null }}>{{ $_app['name'] }}</option>
                                    @endforeach
                                </select>
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
                                    <button type="submit" class="btn btn-primary">
                                        Create
                                    </button>
                                    &nbsp;&nbsp;
                                    <button type="button" class="btn btn-default" onclick="cancel()">
                                        Close
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function cancel(){
            window.location = '/v1/clusters';
        }
    </script>

@stop


