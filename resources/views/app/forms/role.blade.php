<?php
?>
@extends('layouts.main')

@section('content')
    <div role="tabpanel">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Details</a></li>
            <li role="presentation"><a href="#assign" aria-controls="security" role="tab" data-toggle="tab">Assign</a></li>
            <li role="presentation"><a href="#activity" aria-controls="activity" role="tab" data-toggle="tab">Activity</a></li>
        </ul>
        <form class="form-horizontal" action="/api/v1/roles" method="POST">
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="profile">
                    <div class="form-group">
                        <label for="role_name_text" class="col-md-2 control-label">Name</label>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="role_name_text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description_text" class="col-md-2 control-label">Description</label>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="description_text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="home_view_text" class="col-md-2 control-label">Home View</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" id="home_view_text">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-offset-2 col-md-6">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" value="0" id="active_ind">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-offset-2 col-md-10 form-actions">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
@stop