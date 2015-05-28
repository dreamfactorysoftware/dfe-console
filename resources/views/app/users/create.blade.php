
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
                                            <form id="user_form" class="" name="create-user" method="POST" action="/{{$prefix}}/users">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <!-- User Email -->
                                                        <div class="form-group">
                                                            <label>Email</label>
                                                            <input id="email_addr_text" name="email_addr_text" value="" class="form-control" placeholder="Enter email address." type="email" required>
                                                        </div>
                                                        <!-- User First Name -->
                                                        <div class="form-group">
                                                            <label>First Name</label>
                                                            <input id="first_name_text" name="first_name_text" value="" class="form-control" placeholder="Enter first name." type="text" required>
                                                        </div>
                                                        <!-- User Last Name -->
                                                        <div class="form-group">
                                                            <label>Last Name</label>
                                                            <input id="last_name_text" name="last_name_text" value="" class="form-control" placeholder="Enter last name." type="text" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Nickname</label>
                                                            <input id="nickname_text" name="nickname_text" value="" class="form-control" placeholder="Enter nickname." type="text" required>
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
                                                        <!-- User Active -->
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

    <script type="text/javascript" src="../../js/blade-scripts/users/users.js"></script>
@stop






