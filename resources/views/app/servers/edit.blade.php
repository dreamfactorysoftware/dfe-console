
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
                    <div class="ng-scope">
                        <div class="ng-scope">
                            <div class="col-md-2 df-sidebar-nav">
                                <df-sidebar-nav>
                                    <div class="">
                                        <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                                            <li class="ng-scope active">
                                                <a class="" href="/{{$prefix}}/servers">Manage</a>
                                            </li>
                                            <li class="">
                                                <a class="" href="/{{$prefix}}/servers/create">Create</a>
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
                                            <df-section-header class="" data-title="'Manage Servers'">
                                                <div class="df-section-header df-section-all-round">
                                                    <h4 class="">Edit User</h4>
                                                </div>
                                            </df-section-header>


                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <form class="" name="create-user">
                                                        <div class="form-group">
                                                            <label>Name</label>
                                                            <input id="server_name_text" value="{{$server->server_id_text}}" class="form-control" placeholder="Enter email address." type="email">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Type</label>
                                                            <select class="form-control" id="server_type_select">

                                                                @foreach ($server_types as $server_type)

                                                                    @if ($server_type['id'] == $server->server_type_id)
                                                                        <option id="{{$server_type['id']}}" selected="selected">{{$server_type['type_name_text']}}</option>
                                                                    @else
                                                                        <option id="{{$server_type['id']}}">{{$server_type['type_name_text']}}</option>
                                                                    @endif

                                                                @endforeach

                                                            </select>

                                                        </div>
                                                        <div class="form-group">
                                                            <label>Host</label>
                                                            <input id="server_host_text" value="{{$server->host_text}}" class="form-control" placeholder="Enter last name." type="text">
                                                        </div>

                                                        <div id="server_type_db" style="display: none;">
                                                            <div class="form-group">
                                                                <label>Port</label>
                                                                <input id="db_port_text" class="form-control" value="{{ $config['port'] or '' }}" type="text">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>User Name</label>
                                                                <input id="db_username_text" class="form-control" value="{{ $config['username'] or '' }}" type="text">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Password</label>
                                                                <input id="db_password_text" class="form-control" value="{{ $config['password'] or '' }}" type="password">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Driver</label>
                                                                <input id="db_driver_text" class="form-control" value="{{ $config['driver'] or '' }}" type="text">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Default Database Name</label>
                                                                <input id="db_default_db_name_text" class="form-control" value="{{ $config['default-database-name'] or '' }}" type="text">
                                                            </div>
                                                        </div>

                                                        <div id="server_type_web" style="display: none;">
                                                            <div class="form-group">
                                                                <label>Port</label>
                                                                <input id="web_port_text" class="form-control" value="{{ $config['port'] or '' }}" type="text">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Scheme</label>
                                                                <select id="web_scheme_text" class="form-control">
                                                                    <option value="" disabled selected>Select scheme</option>
                                                                    <option value="http">HTTP</option>
                                                                    <option value="https">HTTPS</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>User Name</label>
                                                                <input id="web_username_text" class="form-control" value="{{ $config['username'] or '' }}" type="text">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Password</label>
                                                                <input id="web_password_text" class="form-control" value="{{ $config['password'] or '' }}" type="password">
                                                            </div>
                                                        </div>


                                                        <div id="server_type_app" style="display: none;">
                                                            <div class="form-group">
                                                                <label>Port</label>
                                                                <input id="app_port_text" class="form-control" value="{{ $config['port'] or '' }}" type="text">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Scheme</label>
                                                                <select id="app_scheme_text" class="form-control">
                                                                    <option value="" disabled selected>Select scheme</option>
                                                                    <option value="http">HTTP</option>
                                                                    <option value="https">HTTPS</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>User Name</label>
                                                                <input id="app_username_text" class="form-control" value="{{ $config['username'] or '' }}" type="text">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Password</label>
                                                                <input id="app_password_text" class="form-control" value="{{ $config['password'] or '' }}" type="password">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Access Token</label>
                                                                <input id="app_accesstoken_text" class="form-control" value="{{ $config['access_token'] or '' }}" type="password">
                                                            </div>
                                                        </div>
                                                        </form>

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
                                                            <div class="">

                                                                <button type="button" class="btn btn-primary" onclick="saveEditServer({{$server_id}})">
                                                                    Update
                                                                </button>
                                                                &nbsp;&nbsp;
                                                                <button type="button" class="btn btn-default" onclick="cancelEditServer();">
                                                                    Close
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

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

        });
    </script>

    @stop

