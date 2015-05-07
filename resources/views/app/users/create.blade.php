
@include('layouts.partials.topmenu',array('pageName' => 'Users', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <div class="row">
                    <div class="">
                        <div class="">
                            <div class="col-md-2 df-sidebar-nav">
                                <df-sidebar-nav>
                                    <div class="">
                                        <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                                            <li class="">
                                                <a class="" href="/{{$prefix}}/users">Manage</a>
                                            </li>
                                            <li class="ng-scope active">
                                                <a class="" href="/{{$prefix}}/users/create">Create</a>
                                            </li>
                                        </ul>
                                    </div>
                                </df-sidebar-nav>
                            </div>
                            <div class="col-md-10 df-section df-section-3-round" df-fs-height="">
                                <df-manage-users class=""><div>
                                        <div class="">
                                            <df-section-header class="">
                                                <div class="df-section-header df-section-all-round">
                                                    <h4 class="ng-binding">Create User</h4>
                                                </div>
                                            </df-section-header>


                                            <!-- Create User Form -->
                                            <form id="new_user_form" class="" name="create-user" autocomplete="off">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <!-- User Email -->
                                                        <div class="form-group">
                                                            <label>Email</label>
                                                            <input id="email_addr_text" value="" class="form-control" placeholder="Enter email address." type="email">
                                                        </div>
                                                        <!-- User First Name -->
                                                        <div class="form-group">
                                                            <label>First Name</label>
                                                            <input id="first_name_text" value="" class="form-control" placeholder="Enter first name." type="text">
                                                        </div>
                                                        <!-- User Last Name -->
                                                        <div class="form-group">
                                                            <label>Last Name</label>
                                                            <input id="last_name_text" value="" class="form-control" placeholder="Enter last name." type="text">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Display Name</label>
                                                            <input id="nickname_text" value="" class="form-control" placeholder="Enter display name." type="text">
                                                        </div>
                                                        <df-set-user-password>
                                                            <div class="form-group">
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input id="set_usercreate_password" class="" type="checkbox">
                                                                        Set Password Manually
                                                                    </label>
                                                                </div>
                                                                <div id="set_usercreate_password_form" style="display: none;">
                                                                    <label>Set Password</label>
                                                                    <input id="new_usercreate_password" class="form-control"  placeholder="Enter password" type="password">
                                                                    <span>&nbsp;</span>
                                                                    <input id="retype_new_usercreate_password" class="form-control"  placeholder="Re-enter password" type="password">
                                                                </div>
                                                            </div>

                                                        </df-set-user-password>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>User Options</label>
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input id="system_admin" class="" type="checkbox" onclick="systemAdminClick();">
                                                                    System Administrator
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <!-- User Active -->
                                                        <div class="form-group">
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input id="active" class="" type="checkbox">
                                                                    Active
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <div id="advancedUserOptions" >
                                                            <div class="form-group">
                                                                <label><br>Advanced User Options</label>
                                                                <div class="checkbox">
                                                                    <label id="instance_manage_label">
                                                                        <input id="instance_manage" class="" type="checkbox">
                                                                        Allow User to <b>Create</b> and <b>Delete</b> Instances
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <div class="checkbox">
                                                                    <label id="instance_manage_label">
                                                                        <input id="instance_policy" class="" type="checkbox">
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
                                                            <div class="">

                                                                <button type="button" class="btn btn-primary" onclick="javascript:createUser();">
                                                                    Create
                                                                </button>
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

    <script>
        $('#set_usercreate_password').click(function () {
            if ($('#set_usercreate_password').is(':checked')) {
                $('#set_usercreate_password_form').show();

            } else {
                $('#set_usercreate_password_form').hide();

            }
        });
    </script>

    <script type="text/javascript" src="../../js/blade-scripts/users/users.js"></script>
@stop





