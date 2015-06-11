
@include('layouts.partials.topmenu',array('pageName' => 'Servers', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')

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


        function showValue(newValue)
        {
            var val = newValue / 1000 + ' GB';

            if (newValue > 10000)
                val = 'Unlimited';

            document.getElementById("range").innerHTML=val;
        }

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
                                                <a href="/{{$prefix}}/servers">Manage</a>
                                            </li>
                                            <li class="active">
                                                <a href="/{{$prefix}}/servers/create">Create</a>
                                            </li>
                                        </ul>
                                        <div class="hidden-lg hidden-md" id="sidebar-open">
                                            <button type="button" class="btn btn-default btn-sm"><i class="fa fa-fw fa-bars"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                                    <div class="form-group">
                                                        <label>Name</label>
                                                        <input id="server_id_text" name="server_id_text" class="form-control" placeholder="Enter server name." type="name" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Type</label>
                                                        <select class="form-control" id="server_type_select" name="server_type_select" required>
                                                            <option value="">Select type</option>
                                                            @foreach ($server_types as $server_type)
                                                                <option id="{{$server_type['id']}}">{{$server_type['type_name_text']}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Host</label>
                                                        <input id="host_text" name="host_text" class="form-control" placeholder="Enter host." type="text" required>
                                                    </div>
                                                    <div id="server_type_db" style="display: none;">
                                                        <div class="form-group">
                                                            <label>Port</label>
                                                            <input id="db_port_text" name="config[db][port]" class="form-control" placeholder="Enter port." type="text">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>User Name</label>
                                                            <input id="db_username_text" name="config[db][username]" class="form-control" placeholder="Enter user name." type="text">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Password</label>
                                                            <input id="db_password_text" name="config[db][password]" class="form-control" placeholder="Enter password." type="password">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Driver</label>
                                                            <input id="db_driver_text" name="config[db][driver]" class="form-control" placeholder="Enter database driver." type="text">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Default Database Name</label>
                                                            <input id="db_default_db_name_text" name="config[db][default-database-name]" class="form-control" placeholder="Enter default database name." type="text">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Server Assignment</label><br>
                                                                <input id="db_multi_asgn_text" type="checkbox" id="db_multi_asgn_text" name="config[db][multi-assign]">
                                                                    Allow Multiple Server Assignments
                                                        </div>
                                                    </div>
                                                    <div id="server_type_web" style="display: none;">
                                                        <div class="form-group">
                                                            <label>Port</label>
                                                            <input id="web_port_text" name="config[web][port]" class="form-control" placeholder="Enter port." type="text">
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
                                                            <input id="web_username_text" name="config[web][username]" class="form-control" placeholder="Enter user name." type="text">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Password</label>
                                                            <input id="web_password_text" name="config[web][password]" class="form-control" placeholder="Enter password." type="password">
                                                        </div>
                                                    </div>
                                                    <div id="server_type_app" style="display: none;">
                                                        <div class="form-group">
                                                            <label>Port</label>
                                                            <input id="app_port_text" name="config[app][port]" class="form-control" placeholder="Enter port." type="text">
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
                                                            <input id="app_username_text" name="config[app][username]" class="form-control" placeholder="Enter user name." type="text">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Password</label>
                                                            <input id="app_password_text" name="config[app][password]" class="form-control" placeholder="Enter password." type="password">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Access Token</label>
                                                            <input id="app_accesstoken_text" name="config[app][access_token]" class="form-control" placeholder="Enter access token." type="password">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="../../../js/blade-scripts/servers/servers.js"></script>

@stop


