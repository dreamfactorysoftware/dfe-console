
@include('layouts.partials.topmenu',array('pageName' => 'Servers', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="row">
                    <div>
                        <div>
                            <div class="col-md-2">
                                <div>
                                    <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                                        <li class="active">
                                            <a href="/{{$prefix}}/servers">Manage</a>
                                        </li>
                                        <li>
                                            <a href="/{{$prefix}}/servers/create">Create</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div>
                                    <div>
                                        <div class="nav nav-pills dfe-section-header">
                                            <h4>Edit Server</h4>
                                        </div>
                                    </div>
                                    <form method="POST" action="/{{$prefix}}/servers/{{$server_id}}">
                                        <input name="_method" type="hidden" value="PUT">
                                        <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">
                                        <div class="row">
                                            <div class="col-md-6">
                                                @if(Session::has('flash_message'))
                                                    <p class="alert {{ Session::get('flash_type') }}">{{ Session::get('flash_message') }}</p>
                                                @endif
                                                <form name="create-user">
                                                <div class="form-group">
                                                    <label>Name</label>
                                                    <input id="server_name_text" name="server_id_text" @if (Input::old('server_id_text')) value="{{ Input::old('server_id_text') }}" @else value="{{ $server->server_id_text }}" @endif class="form-control" placeholder="Enter email address.">
                                                </div>
                                                <div class="form-group">
                                                    <label>Type</label>
                                                        @foreach ($server_types as $server_type)
                                                            @if ($server_type['id'] == $server->server_type_id)
                                                                <input id="server_type_select" name="server_type_select" value="{{$server_type['type_name_text']}}" class="form-control" placeholder="" readonly>
                                                            @endif
                                                        @endforeach
                                                </div>
                                                <div class="form-group">
                                                    <label>Host</label>
                                                    <input id="server_host_text" name="host_text" @if (Input::old('host_text')) value="{{ Input::old('host_text') }}" @else value="{{$server->host_text}}" @endif class="form-control" placeholder="Enter last name." type="text">
                                                </div>

                                                <div id="server_type_db" style="display: none;">
                                                    <div class="form-group">
                                                        <label>Port</label>
                                                        <input id="db_port_text" name="config[db][port]" class="form-control" @if (Input::old('config.db.port')) value="{{ Input::old('config.db.port') }}" @else value="{{ $config['port'] or '' }}" @endif type="text">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>User Name</label>
                                                        <input id="db_username_text" name="config[db][username]" class="form-control" @if (Input::old('config.db.username')) value="{{ Input::old('config.db.username') }}" @else value="{{ $config['username'] or '' }}" @endif type="text">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Password</label>
                                                        <input id="db_password_text" name="config[db][password]" class="form-control" @if (Input::old('config.db.password')) value="{{ Input::old('config.db.password') }}" @else value="{{ $config['password'] or '' }}" @endif type="password">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Driver</label>
                                                        <input id="db_driver_text" name="config[db][driver]" class="form-control" @if (Input::old('config.db.driver')) value="{{ Input::old('config.db.driver') }}" @else value="{{ $config['driver'] or '' }}" @endif type="text">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Default Database Name</label>
                                                        <input id="db_default_db_name_text" name="config[db][default-database-name]" class="form-control" @if (Input::old('config.db.default-database-name')) value="{{ Input::old('config.db.default-database-name') }}" @else value="{{ $config['default-database-name'] or '' }}" @endif type="text">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Server Assignment</label><br>
                                                        <input id="db_multi_asgn_text" type="checkbox" id="db_multi_asgn_text" name="config[db][multi-assign]" @if (isset($config['multi-assign'])) checked="checked" @endif>
                                                        Allow Multiple Server Assignments
                                                    </div>
                                                </div>

                                                <div id="server_type_web" style="display: none;">
                                                    <div class="form-group">
                                                        <label>Port</label>
                                                        <input id="web_port_text" name="config[web][port]" class="form-control" @if (Input::old('config.web.port')) value="{{ Input::old('config.web.port') }}" @else value="{{ $config['port'] or '' }}" @endif type="text">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Scheme</label>
                                                        <select id="web_scheme_text" name="config[web][scheme]" class="form-control">
                                                            <option value="" disabled selected>Select scheme</option>
                                                            <option value="http">HTTP</option>
                                                            <option value="https">HTTPS</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>User Name</label>
                                                        <input id="web_username_text" name="config[web][username]" class="form-control" @if (Input::old('config.web.username')) value="{{ Input::old('config.web.username') }}" @else value="{{ $config['username'] or '' }}" @endif type="text">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Password</label>
                                                        <input id="web_password_text" name="config[web][password]" class="form-control" @if (Input::old('config.web.password')) value="{{ Input::old('config.web.password') }}" @else value="{{ $config['password'] or '' }}" @endif type="password">
                                                    </div>
                                                </div>


                                                <div id="server_type_app" style="display: none;">
                                                    <div class="form-group">
                                                        <label>Port</label>
                                                        <input id="app_port_text" name="config[app][port]" class="form-control" @if (Input::old('config.app.port')) value="{{ Input::old('config.app.port') }}" @else value="{{ $config['port'] or '' }}" @endif type="text">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Scheme</label>
                                                        <select id="app_scheme_text" name="config[app][scheme]" class="form-control">
                                                            <option value="" disabled selected>Select scheme</option>
                                                            <option value="http">HTTP</option>
                                                            <option value="https">HTTPS</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>User Name</label>
                                                        <input id="app_username_text" name="config[app][username]" class="form-control" @if (Input::old('config.app.username')) value="{{ Input::old('config.app.username') }}" @else value="{{ $config['username'] or '' }}" @endif type="text">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Password</label>
                                                        <input id="app_password_text" name="config[app][password]" class="form-control" @if (Input::old('config.app.password')) value="{{ Input::old('config.app.password') }}" @else value="{{ $config['username'] or '' }}" @endif type="password">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Access Token</label>
                                                        <input id="app_accesstoken_text" name="config[app][access_token]" class="form-control" @if (Input::old('config.app.access_token')) value="{{ Input::old('config.app.access_token') }}" @else value="{{ $config['access_token'] or '' }}" @endif type="password">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Storage Limit</label>
                                                    <div style="text-align: center">
                                                    <input type="range" min="0" max="10100" value="500" step="100" onchange="showValue(this.value)" />
                                                        <span id="range">0.5 GB</span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    &nbsp;
                                                </div>
                                                <div class="form-group">
                                                    <label>Cluster</label>
                                                    <div style="text-align: left">
                                                        <span>Assigned to:&nbsp;&nbsp;&nbsp;<b>{{$clusters}}</b></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <hr>
                                                <div class="form-group">
                                                    <div>
                                                        <button type="submit" class="btn btn-primary">Update</button>
                                                        &nbsp;&nbsp;
                                                        <button type="button" class="btn btn-default" onclick="cancelEditServer();">
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="../../../js/blade-scripts/servers/servers.js"></script>

    <script type='text/javascript'>

        $( document ).ready(function() {

            var txt = "{{ $config['scheme'] or ''}}";

            if(txt !== ''){
                $('#app_scheme_text option')
                    .filter(function() { return $.trim( $(this).text() ) == txt.toUpperCase(); })
                    .attr('selected',true);

                $('#web_scheme_text option')
                        .filter(function() { return $.trim( $(this).text() ) == txt.toUpperCase(); })
                        .attr('selected',true);
            }

            @foreach ($server_types as $server_type)
                @if ($server_type['id'] == $server->server_type_id)
                    $("#server_type_{{$server_type['type_name_text']}}").show();
                @else
                    $("#server_type_{{$server_type['type_name_text']}}").hide();
                @endif
            @endforeach

        });

        function showValue(newValue)
        {
            var val = newValue / 1000 + ' GB';

            if (newValue > 10000)
                val = 'Unlimited';

            document.getElementById("range").innerHTML=val;
        }

    </script>

    @stop

