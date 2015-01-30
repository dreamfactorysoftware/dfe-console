<?php
?>
@extends('layouts.main')

@section('content')
    <form class="form-horizontal" action="form-submit">

        <div class="form-group">
            <label for="first_name_text" class="col-sm-2 control-label">First Name</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="first_name_text">
            </div>
        </div>

        <div class="form-group">
            <label for="last_name_text" class="col-sm-2 control-label">Last Name</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="last_name_text">
            </div>
        </div>

        <div class="form-group">
            <label for="email_addr_text" class="col-sm-2 control-label">Email Address</label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="email_addr_text">
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-default">Save Changes</button>
            </div>
        </div>

    </form>
@stop