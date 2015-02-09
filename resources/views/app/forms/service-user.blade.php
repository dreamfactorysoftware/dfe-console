<?php
?>
@extends('layouts.main')

@section('content')
    <div role="tabpanel">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Profile</a>
            </li>
            <li role="presentation"><a href="#security" aria-controls="security" role="tab" data-toggle="tab">Security</a></li>
            <li role="presentation"><a href="#activity" aria-controls="activity" role="tab" data-toggle="tab">Activity</a></li>
            <li role="presentation"><a href="#external" aria-controls="external" role="tab" data-toggle="tab">External</a></li>
        </ul>
        <form class="form-horizontal">
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="profile">
                    <div class="form-group">
                        <label for="first_name_text" class="col-md-2 control-label">First Name</label>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="first_name_text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="last_name_text" class="col-md-2 control-label">Last Name</label>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="last_name_text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="display_name_text" class="col-md-2 control-label">Display Name</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="display_name_text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email_addr_text" class="col-md-2 control-label">Email Address</label>
                        <div class="col-md-6">
                            <input type="email" class="form-control" id="email_addr_text" placeholder="user@domain.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_text" class="col-md-2 control-label">Password</label>
                        <div class="col-md-6">
                            <input type="password" class="form-control" id="password_text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm_text" class="col-md-2 control-label">Password (Confirm)</label>
                        <div class="col-md-6">
                            <input type="password" class="form-control" id="password_confirm_text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="company_name_text" class="col-md-2 control-label">Company Name</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="company_name_text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="title_text" class="col-md-2 control-label">Title</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="title_text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="city_text" class="col-md-2 control-label">City</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="city_text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="state_province_text" class="col-md-2 control-label">State</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="state_province_text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="country_text" class="col-md-2 control-label">Country</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="country_text">
                        </div>
                    </div>

                </div>

                <div role="tabpanel" class="tab-pane" id="security">Coming</div>

                <div role="tabpanel" class="tab-pane" id="activity">Coming</div>

                <div role="tabpanel" class="tab-pane" id="external">Coming</div>
            </div>
            <div class="form-group">
                <div class="col-md-offset-2 col-md-10 form-actions">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
    {{--id drupal_id api_token_text drupal_password_text owner_id owner_type_nbr company_name_text title_text city_text state_province_text country_text postal_code_text phone_text fax_text opt_in_ind agree_ind valid_email_hash_text valid_email_hash_expire_time valid_email_date recover_hash_text recover_hash_expire_time last_login_date last_login_ip_text admin_ind storage_id_text activate_ind create_date lmod_date--}}
@stop