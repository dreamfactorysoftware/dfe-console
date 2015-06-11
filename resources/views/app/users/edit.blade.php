
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
                                        <li class="active">
                                            <a href="/{{$prefix}}/users">Manage</a>
                                        </li>
                                        <li>
                                            <a href="/{{$prefix}}/users/create">Create</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <div>
                                    <div>
                                        <div>
                                            <div class="nav nav-pills dfe-section-header">
                                                <h4>Edit User</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <form id="user_form" method="POST" action="/{{$prefix}}/users/{{$user_id}}">
                                        <input name="_method" type="hidden" value="PUT">
                                        <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">
                                        <input name="user_type" type="hidden" id="user_type" value="{{ $is_admin }}">
                                        <input name="user_auth" type="hidden" id="user_auth" value="{{ Auth::user()->id }}">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input id="email_addr_text" name="email_addr_text" value="{{$user->email_addr_text}}" class="form-control" placeholder="Enter email address." type="email" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>First Name</label>
                                                    <input id="first_name_text" name="first_name_text" value="{{$user->first_name_text}}" class="form-control" placeholder="Enter first name." type="text" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Last Name</label>
                                                    <input id="last_name_text" name="last_name_text" value="{{$user->last_name_text}}" class="form-control" placeholder="Enter last name." type="text" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Nickname</label>
                                                    <input id="nickname_text" name="nickname_text" value="{{$user->nickname_text}}" class="form-control" placeholder="Enter nickname." type="text" required>
                                                </div>
                                                <div class="form-group">
                                                    <div id="set_password_form">
                                                        <label>Set Password</label>
                                                        <input id="new_password" name="new_password" class="form-control" value="{{$user->password_text}}" placeholder="Enter password" type="password" required>
                                                        <div>&nbsp;</div>
                                                        <input id="retype_new_password" class="form-control" value="{{$user->password_text}}" placeholder="Re-enter password" type="password" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>User Options</label>
                                                    <div class="checkbox">
                                                        <label>
                                                            <input id="system_admin" name="system_admin" value="1" type="checkbox" disabled onclick="systemAdminClick();" @if($is_admin) checked @endif>
                                                            System Administrator
                                                        </label>
                                                    </div>
                                                </div>
                                                <!-- User Active -->
                                                <div class="form-group">
                                                    <div class="checkbox">
                                                        <label>
                                                            @if( Auth::user()->id != $user->id )
                                                                <input id="active_ind" name="active_ind" value="1" type="checkbox" @if($user->active_ind) checked @endif>
                                                            @else
                                                                <input id="active_ind" name="active_ind" value="1" type="checkbox" disabled @if($user->active_ind) checked @endif>
                                                            @endif
                                                            Active
                                                        </label>
                                                    </div>
                                                </div>


                                                <div id="advancedUserOptions" >
                                                    <div class="form-group">
                                                        <label><br>Advanced User Options</label>
                                                        <div class="checkbox">
                                                            <label>
                                                                <input id="instance_manage_ind" name="instance_manage_ind" @if($user->instance_manage_ind) checked @endif type="checkbox">
                                                                Allow User to <b>Create</b> and <b>Delete</b> Instances
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <!-- User Active -->
                                                    <div class="form-group">
                                                        <div class="checkbox">
                                                            <label>
                                                                <input id="instance_policy_ind" name="instance_policy_ind" @if($user->instance_policy_ind) checked @endif type="checkbox">
                                                                Allow User to <b>Change</b> Instance Policies
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
                                                        <input type="submit" id="btnSubmitForm" value="Update" class="btn btn-primary">
                                                        &nbsp;&nbsp;
                                                        <button type="button" class="btn btn-default" onclick="javascript:cancelEditUser();">
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

    <script type="text/javascript" src="../../../js/blade-scripts/users/users.js"></script>

    <script type='text/javascript'>

        $( document ).ready(function() {

            <?php
                if($is_admin)
                    echo "initUserEditSet(false);";
                else
                    echo "initUserEditSet(true);";
            ?>

        });

    </script>


@stop


