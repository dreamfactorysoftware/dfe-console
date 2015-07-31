@include('layouts.partials.topmenu')
@extends('layouts.main')
@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'servers'])


        <div class="col-md-10">
            <div>
                <div>
                    <div>
                        <div class="nav nav-pills dfe-section-header">
                            <h4>Create Server</h4>
                        </div>
                    </div>
                    <form method="POST" action="/{{$prefix}}/servers">
                        <input name="_method" type="hidden" value="POST">
                        <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">

                        <div class="row">
                            <div class="col-md-6">
                                @if(Session::has('flash_message'))
                                    <p class="alert {{ Session::get('flash_type') }}">{{ Session::get('flash_message') }}</p>
                                @endif
                                <div class="form-group">
                                    <label>Name</label>
                                    <input id="server_id_text" name="server_id_text" class="form-control" placeholder="Enter server name." type="name" value="{{ Input::old('server_id_text') }}">
                                </div>
                                <div class="form-group">
                                    <label>Type</label>
                                    <select class="form-control" id="server_type_select" name="server_type_select">
                                        <option value="">Select type</option>
                                        @foreach ($server_types as $server_type)
                                            <option id="{{$server_type['id']}}" @if (Input::old('server_type_select') == $server_type['type_name_text']) selected="selected" @endif>{{$server_type['type_name_text']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Host</label>
                                    <input id="host_text" name="host_text" class="form-control" placeholder="Enter host." type="text" value="{{ Input::old('host_text') }}">
                                </div>
                                <div id="server_type_db" style="display: none;">
                                    <div class="form-group">
                                        <label>Port</label>
                                        <input id="db_port_text" name="config[db][port]" class="form-control" placeholder="Enter port." value="{{ Input::old('config.db.port') }}" type="text">
                                    </div>
                                    <div class="form-group">
                                        <label>User Name</label>
                                        <input id="db_username_text" name="config[db][username]" class="form-control" placeholder="Enter user name." value="{{ Input::old('config.db.username') }}" type="text">
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input id="db_password_text" name="config[db][password]" class="form-control" placeholder="Enter password." value="{{ Input::old('config.db.password') }}" type="password">
                                    </div>
                                    <div class="form-group">
                                        <label>Driver</label>
                                        <input id="db_driver_text" name="config[db][driver]" class="form-control" placeholder="Enter database driver." value="{{ Input::old('config.db.driver') }}" type="text">
                                    </div>
                                    <div class="form-group">
                                        <label>Database Name</label>
                                        <input id="db_default_db_name_text" name="config[db][default-database-name]" class="form-control" placeholder="Enter database name." value="{{ Input::old('config.db.default-database-name') }}" type="text">
                                    </div>
                                </div>
                                <div id="server_type_web" style="display: none;">
                                    <div class="form-group">
                                        <label>Port</label>
                                        <input id="web_port_text" name="config[web][port]" class="form-control" placeholder="Enter port." value="{{ Input::old('config.web.port') }}" type="text">
                                    </div>
                                    <div class="form-group">
                                        <label>Protocol</label>
                                        <select id="web_scheme_text" name="config[web][scheme]" class="form-control">
                                            <option value="" selected>Select protocol</option>
                                            <option value="http" @if (Input::old('config.web.scheme') == 'http') selected="selected" @endif>HTTP</option>
                                            <option value="https" @if (Input::old('config.web.scheme') == 'https') selected="selected" @endif>HTTPS</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>User Name</label>
                                        <input id="web_username_text" name="config[web][username]" class="form-control" placeholder="Enter user name." value="{{ Input::old('config.web.username') }}" type="text">
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input id="web_password_text" name="config[web][password]" class="form-control" placeholder="Enter password." value="{{ Input::old('config.web.password') }}" type="password">
                                    </div>
                                </div>
                                <div id="server_type_app" style="display: none;">
                                    <div class="form-group">
                                        <label>Port</label>
                                        <input id="app_port_text" name="config[app][port]" class="form-control" placeholder="Enter port." value="{{ Input::old('config.app.port') }}" type="text">
                                    </div>
                                    <div class="form-group">
                                        <label>Protocol</label>
                                        <select id="app_scheme_text" name="config[app][scheme]" class="form-control">
                                            <option value="" selected>Select protocol</option>
                                            <option value="http" @if (Input::old('config.app.scheme') == 'http') selected="selected" @endif>HTTP</option>
                                            <option value="https" @if (Input::old('config.app.scheme') == 'https') selected="selected" @endif>HTTPS</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>User Name</label>
                                        <input id="app_username_text" name="config[app][username]" class="form-control" placeholder="Enter user name." value="{{ Input::old('config.app.username') }}" type="text">
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input id="app_password_text" name="config[app][password]" class="form-control" placeholder="Enter password." value="{{ Input::old('config.app.password') }}" type="password">
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
                                        <button type="submit" class="btn btn-primary">Create</button>
                                        &nbsp;&nbsp;
                                        <button type="button" class="btn btn-default" onclick="cancelCreateServer();">
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



    <script type="text/javascript" src="../../../js/blade-scripts/servers/servers.js"></script>

    <script type='text/javascript'>

        $(document.body).on('change','#server_type_select',function(){

            var selected = $('#server_type_select').val();

            $("#server_type_select option").each(function()
            {
                var opt = $(this).val();

                if(opt !== ''){
                    if(opt === selected)
                        $('#server_type_' + opt).show();
                    else
                        $('#server_type_' + opt).hide();
                }
            });
        });


    </script>

@stop


