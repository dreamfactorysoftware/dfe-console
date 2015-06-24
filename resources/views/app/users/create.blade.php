
@include('layouts.partials.topmenu',array('pageName' => 'Users', 'prefix' => $prefix))

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
                                        <li>
                                            <a href="/{{$prefix}}/users">Manage</a>
                                        </li>
                                        <li class="active">
                                            <a href="/{{$prefix}}/users/create">Create</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div>
                                    <div>
                                        <div class="nav nav-pills dfe-section-header">
                                            <h4>Create User</h4>
                                        </div>
                                    </div>
                                    <form id="user_form" name="create-user" method="POST" action="/{{$prefix}}/users">
                                        <div class="row">
                                            <div class="col-md-6">
                                                @if(Session::has('flash_message'))
                                                    <p class="alert {{ Session::get('flash_type') }}">{{ Session::get('flash_message') }}</p>
                                                @endif
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input id="email_addr_text" name="email_addr_text" class="form-control" placeholder="Enter email address." type="email" value="{{ Input::old('email_addr_text') }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>First Name</label>
                                                    <input id="first_name_text" name="first_name_text" class="form-control" placeholder="Enter first name." type="text" value="{{ Input::old('first_name_text') }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Last Name</label>
                                                    <input id="last_name_text" name="last_name_text" class="form-control" placeholder="Enter last name." type="text" value="{{ Input::old('last_name_text') }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Nickname</label>
                                                    <input id="nickname_text" name="nickname_text" class="form-control" placeholder="Enter nickname." type="text" value="{{ Input::old('nickname_text') }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <div id="">
                                                        <label>Set Password</label>
                                                        <input id="new_password" name="new_password" class="form-control" value="" placeholder="Enter password." type="password" required>
                                                        <span>&nbsp;</span>
                                                        <input id="retype_new_password" class="form-control"  placeholder="Re-enter password." type="password"required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>User Options</label>
                                                    <div class="checkbox">
                                                        <label>
                                                            <input id="system_admin" name="system_admin" value="1" type="checkbox" onclick="systemAdminClick();">
                                                            System Administrator
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input id="active" name="active" value="1" type="checkbox">
                                                            Active
                                                        </label>
                                                    </div>
                                                </div>
                                                <div id="advancedUserOptions" >
                                                    <div class="form-group">
                                                        <label><br>Advanced User Options</label>
                                                        <div class="checkbox">
                                                            <label id="instance_manage_label">
                                                                <input id="instance_manage" type="checkbox">
                                                                Allow User to <b>Create</b> and <b>Delete</b> Instances
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="checkbox">
                                                            <label id="instance_manage_label">
                                                                <input id="instance_policy" type="checkbox">
                                                                Allow User to Change Instance Policies
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <hr>
                                                <div class="form-group">
                                                    <div>
                                                        <input type="submit" id="btnSubmitForm" value="Create" class="btn btn-primary">
                                                        &nbsp;&nbsp;
                                                        <button type="button" class="btn btn-default" onclick="javascript:cancelCreateUser();">
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

    <script type="text/javascript" src="../../js/blade-scripts/users/users.js"></script>
@stop






