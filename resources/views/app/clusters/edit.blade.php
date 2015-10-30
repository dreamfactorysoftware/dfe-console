@include('layouts.partials.topmenu')
@extends('layouts.main')
@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'clusters'])

    <div class="col-md-10">

        <div>
            <div>
                <div>
                    <div class="nav nav-pills dfe-section-header">
                        <h4>Edit Cluster</h4>
                    </div>
                </div>

                <form method="POST" action="/{{$prefix}}/clusters/{{$cluster_id}}">
                    <input name="_method" type="hidden" value="PUT">
                    <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">
                    <input id="_server_list" name="_server_list" type="hidden" value="">

                    <div class="row">

                        <div class="col-md-6">
                            @if(Session::has('flash_message'))
                                <p class="alert {{ Session::get('flash_type') }}">{{ Session::get('flash_message') }}</p>
                            @endif
                            <div class="form-group">
                                <label class="control-label" for="cluster_id_text">Name</label>
                                <input id="cluster_id_text"
                                       name="cluster_id_text"
                                       class="form-control"
                                       @if (Input::old('cluster_id_text')) value="{{ Input::old('cluster_id_text') }}"
                                       @else value="{{ $cluster->cluster_id_text or '' }}" @endif
                                       type="text">
                            </div>
                            <div class="form-group">
                                <label class="control-label" for="subdomain_text">Fixed DNS Subdomain</label>
                                <input id="subdomain_text"
                                       name="subdomain_text"
                                       class="form-control"
                                       @if (Input::old('subdomain_text')) value="{{ Input::old('subdomain_text') }}"
                                       @else value="{{ $cluster->subdomain_text or '' }}" @endif
                                       type="text">
                            </div>
                            <div class="form-group">
                                <label>Select Web Server</label>
                                <select class="form-control" id="web_server_id" name="web_server_id">
                                    <option value="{{$datas['web']['id']}}" selected>{{$datas['web']['name']}}</option>
                                    @foreach ($web as $_web)
                                        <option value="{{ $_web['id'] }}" {{ Input::old('web_server_id') == $_web['id'] ? 'selected="selected"' : null }}>{{ $_web['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select Database Server</label>
                                <select class="form-control" id="db_server_id" name="db_server_id">
                                    <option value="{{$datas['db']['id']}}" selected>{{$datas['db']['name']}}</option>
                                    @foreach ($db as $_db)
                                        <option value="{{ $_db['id'] }}" {{ Input::old('db_server_id') == $_db['id'] ? 'selected="selected"' : null }}>{{ $_db['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select App Server</label>
                                <select class="form-control" id="app_server_id" name="app_server_id">
                                    <option value="{{$datas['app']['id']}}" {{ Input::old('app_server_id') ? null : 'selected="selected"' }}>{{$datas['app']['name']}}</option>
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
                                        Update
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
        function cancel() {
            window.location = '/v1/clusters';
        }
    </script>

@stop


