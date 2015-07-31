@include('layouts.partials.topmenu')
@extends('layouts.main')
@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'users'])

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
                                <input id="email_addr_text" name="email_addr_text" class="form-control" placeholder="Enter email address." type="email" value="{{ Input::old('email_addr_text') }}">
                            </div>
                            <div class="form-group">
                                <label>First Name</label>
                                <input id="first_name_text" name="first_name_text" class="form-control" placeholder="Enter first name." type="text" value="{{ Input::old('first_name_text') }}">
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input id="last_name_text" name="last_name_text" class="form-control" placeholder="Enter last name." type="text" value="{{ Input::old('last_name_text') }}">
                            </div>
                            <div class="form-group">
                                <label>Display Name</label>
                                <input id="nickname_text" name="nickname_text" class="form-control" placeholder="Enter display name." type="text" value="{{ Input::old('nickname_text') }}">
                            </div>
                            <div class="form-group">
                                <div id="">
                                    <label>Set Password</label>
                                    <input id="new_password" name="new_password" class="form-control" value="" placeholder="Enter password." type="password">
                                    <span>&nbsp;</span>
                                    <input id="retype_new_password" class="form-control"  placeholder="Re-enter password." type="password">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>User Options</label>
                                <div class="checkbox">
                                    <label>
                                        <input id="system_admin" name="system_admin" value="1" type="checkbox">
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


    <script type="text/javascript" src="../../js/blade-scripts/users/users.js"></script>
@stop






