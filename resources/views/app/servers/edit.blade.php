@include('layouts.partials.topmenu')
@extends('layouts.main')
@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'servers'])


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
                                <input id="server_name_text" name="server_id_text" @if (Input::old('server_id_text')) value="{{ Input::old('server_id_text') }}" @else value="{{ $server->server_id_text }}" @endif class="form-control" placeholder="Enter server name.">
                            </div>
                            <div class="form-group">
                                <label>Cluster</label>
                                <input class="form-control" value="{{ $clusters }}" type="text" readonly>
                            </div>
                            <div class="form-group">
                                <label>Host</label>
                                <input id="server_host_text" name="host_text" @if (Input::old('host_text')) value="{{ Input::old('host_text') }}" @else value="{{$server->host_text}}" @endif class="form-control" placeholder="Enter host." type="text">
                            </div>
                            <div class="form-group">
                                <label>Type</label>
                                    @foreach ($server_types as $server_type)
                                        @if ($server_type['id'] == $server->server_type_id)
                                            <input id="server_type_select" name="server_type_select" value="{{$server_type['type_name_text']}}" class="form-control" placeholder="" readonly>
                                        @endif
                                    @endforeach
                            </div>
                            <div id="server_type_db" style="display: none;">
                                <div class="form-group">
                                    <label>Port</label>
                                    <input id="db_port_text" name="config[db][port]" class="form-control" @if (Input::old('config.db.port')) value="{{ Input::old('config.db.port') }}" @else value="{{ $config['port'] or '' }}" @endif type="text">
                                </div>
                                <div class="form-group">
                                    <label>User Name</label>
                                    <input id="db_username_text" name="config[db][username]" placeholder="Enter user name." class="form-control" @if (Input::old('config.db.username')) value="{{ Input::old('config.db.username') }}" @else value="{{ $config['username'] or '' }}" @endif type="text">
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input id="db_password_text" name="config[db][password]" placeholder="Enter password."class="form-control" @if (Input::old('config.db.password')) value="{{ Input::old('config.db.password') }}" @else value="{{ $config['password'] or '' }}" @endif type="password">
                                </div>
                                <div class="form-group">
                                    <label>Driver</label>
                                    <input id="db_driver_text" name="config[db][driver]" placeholder="Enter driver." class="form-control" @if (Input::old('config.db.driver')) value="{{ Input::old('config.db.driver') }}" @else value="{{ $config['driver'] or '' }}" @endif type="text">
                                </div>
                                <div class="form-group">
                                    <label>Database Name</label>
                                    <input id="db_default_db_name_text" name="config[db][default-database-name]" placeholder="Enter database name." class="form-control" @if (Input::old('config.db.default-database-name')) value="{{ Input::old('config.db.default-database-name') }}" @else value="{{ $config['default-database-name'] or '' }}" @endif type="text">
                                </div>
                            </div>

                            <div id="server_type_web" style="display: none;">
                                <div class="form-group">
                                    <label>Port</label>
                                    <input id="web_port_text" name="config[web][port]" class="form-control" placeholder="Enter port." @if (Input::old('config.web.port')) value="{{ Input::old('config.web.port') }}" @else value="{{ $config['port'] or '' }}" @endif type="text">
                                </div>
                                <div class="form-group">
                                    <label>Protocol</label>
                                    <select id="web_scheme_text" name="config[web][scheme]" class="form-control">
                                        <option value="http" @if (Input::old('config.web.scheme') == 'http') selected @elseif (isset($config['scheme'])) @if ($config['scheme'] == 'http') selected @endif @endif>HTTP</option>
                                        <option value="https" @if (Input::old('config.web.scheme') == 'https') selected @elseif (isset($config['scheme'])) @if ($config['scheme'] == 'https') selected @endif @endif>HTTPS</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>User Name</label>
                                    <input id="web_username_text" name="config[web][username]" class="form-control" placeholder="Enter user name." @if (Input::old('config.web.username')) value="{{ Input::old('config.web.username') }}" @else value="{{ $config['username'] or '' }}" @endif type="text">
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input id="web_password_text" name="config[web][password]" class="form-control" placeholder="Enter password." @if (Input::old('config.web.password')) value="{{ Input::old('config.web.password') }}" @else value="{{ $config['password'] or '' }}" @endif type="password">
                                </div>
                            </div>


                            <div id="server_type_app" style="display: none;">
                                <div class="form-group">
                                    <label>Port</label>
                                    <input id="app_port_text" name="config[app][port]" class="form-control" placeholder="Enter port." @if (Input::old('config.app.port')) value="{{ Input::old('config.app.port') }}" @else value="{{ $config['port'] or '' }}" @endif type="text">
                                </div>
                                <div class="form-group">
                                    <label>Protocol</label>
                                    <select id="app_scheme_text" name="config[app][scheme]" class="form-control">
                                        <option value="http" @if (Input::old('config.app.scheme') == 'http') selected @elseif (isset($config['scheme'])) @if ($config['scheme'] == 'http') selected @endif @endif>HTTP</option>
                                        <option value="https" @if (Input::old('config.app.scheme') == 'https') selected @elseif (isset($config['scheme'])) @if ($config['scheme'] == 'https') selected @endif @endif>HTTPS</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>User Name</label>
                                    <input id="app_username_text" name="config[app][username]" class="form-control" placeholder="Enter user name." @if (Input::old('config.app.username')) value="{{ Input::old('config.app.username') }}" @else value="{{ $config['username'] or '' }}" @endif type="text">
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input id="app_password_text" name="config[app][password]" class="form-control" placeholder="Enter password." @if (Input::old('config.app.password')) value="{{ Input::old('config.app.password') }}" @else value="{{ $config['username'] or '' }}" @endif type="password">
                                </div>
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

    </script>

    @stop

