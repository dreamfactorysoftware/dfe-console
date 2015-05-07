
@include('layouts.partials.topmenu',array('pageName' => 'Users', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')

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
                                                <a class="" href="/{{$prefix}}/users">Manage</a>
                                            </li>
                                            <li class="">
                                                <a class="" href="/{{$prefix}}/users/create">Create</a>
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
                                            <df-section-header class="" data-title="'Manage Users'">
                                                <div class="df-section-header df-section-all-round">
                                                    <h4 class="">Edit User</h4>
                                                </div>
                                            </df-section-header>

                                            <form class="" name="create-user">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Email</label>
                                                            <input id="email_addr_text" value="{{$user->email_addr_text}}" class="form-control" placeholder="Enter email address." type="email">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>First Name</label>
                                                            <input id="first_name_text" value="{{$user->first_name_text}}" class="form-control" placeholder="Enter first name." type="text">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Last Name</label>
                                                            <input id="last_name_text" value="{{$user->last_name_text}}" class="form-control" placeholder="Enter last name." type="text">
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Display Name</label>
                                                            <input id="nickname_text" value="{{$user->nickname_text}}" class="form-control" placeholder="Enter display name." type="text">
                                                        </div>
                                                        <df-set-user-password>
                                                            <div class="form-group">
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input id="set_password" class="" type="checkbox">
                                                                        Set Password Manually
                                                                    </label>
                                                                </div>
                                                                <div id="set_password_form" style="display: none;">
                                                                    <label>Set Password</label>
                                                                    <input id="new_password" class="form-control"  placeholder="Enter password" type="password">
                                                                    <input id="retype_new_password" class="form-control"  placeholder="Re-enter password" type="password">
                                                                </div>
                                                            </div>

                                                        </df-set-user-password>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>User Options</label>
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input id="system_admin" class="" type="checkbox" disabled onclick="systemAdminClick();" @if($is_admin) checked @endif>
                                                                    System Administrator
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <!-- User Active -->
                                                        <div class="form-group">
                                                            <div class="checkbox">
                                                                <label>
                                                                    <input id="active" class="" type="checkbox" checked>
                                                                    Active
                                                                </label>
                                                            </div>
                                                        </div>


                                                        <div id="advancedUserOptions" >
                                                            <div class="form-group">
                                                                <label><br>Advanced User Options</label>
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input id="instance_manage" class="" type="checkbox">
                                                                        Allow User to <b>Create</b> and <b>Delete</b> Instances
                                                                    </label>
                                                                </div>
                                                            </div>
                                                            <!-- User Active -->
                                                            <div class="form-group">
                                                                <div class="checkbox">
                                                                    <label>
                                                                        <input id="instance_policy" class="" type="checkbox">
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
                                                            <div class="">

                                                                <button type="button" class="btn btn-primary" onclick="javascript:editUser({{$user_id}});">
                                                                    Update
                                                                </button>
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

    <script type="text/javascript" src="../../../js/blade-scripts/users/users.js"></script>

    <script type='text/javascript'>

        $( document ).ready(function() {

            <?php

                if($is_admin)
                    echo "initUserEditSet(false);";
                else
                    echo "initUserEditSet(true);";
            ?>


            $('#set_password').click(function () {
                if ($('#set_password').is(':checked')) {
                    $('#set_password_form').show();

                } else {
                    $('#set_password_form').hide();

                }
            });

        });

    </script>


@stop

