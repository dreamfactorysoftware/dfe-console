@include('layouts.partials.topmenu')
@extends('layouts.main')
@section('content')
    @include('layouts.partials.sidebar-menu',['resource'=>'users'])

    <div class="col-md-10">
        <div>
            <div>
                <div class="nav nav-pills dfe-section-header">
                    <h4>Manage Users</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <form method="POST" action="/{{$prefix}}/users/multi" id="multi_delete">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">
                <input name="_selectedIds" id="_selectedIds" type="hidden" value="">
                <input name="_selectedTypes" id="_selectedTypes" type="hidden" value="">

                <div class="col-xs-12">
                    <div class="well well-sm">
                        <div class="btn-group btn-group pull-right">

                        </div>
                        <div class="btn-group btn-group">

                            <button type="button" disabled="true" class="btn btn-default btn-sm fa fa-fw fa-backward"
                                    id="_prev" style="height: 30px; width: 40px"></button>

                            <div class="btn-group">
                                <button type="button" class="btn btn-default dropdown-toggle btn-sm"
                                        data-toggle="dropdown" aria-expanded="false">
                                    <span id="currentPage">Page 1</span> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu" id="tablePages">
                                </ul>
                            </div>

                            <button type="button" disabled="true" class="btn btn-default btn-sm fa fa-fw fa-forward"
                                    id="_next" style="height: 30px; width: 40px"></button>
                        </div>
                        <div class="btn-group">
                            <button type="button" id="selectedUsersRemove"
                                    class="btn btn-default btn-sm fa fa-fw fa-trash" title="Delete selected clusters"
                                    value="delete" style="width: 40px"></button>
                        </div>
                        <div class="btn-group">
                            <input id="userSearch" class="form-control input-sm" value="" type="text"
                                   placeholder="Search Users...">
                            <span id="searchclear" class="glyphicon glyphicon-remove-circle" style="display:none;"></span>

                        </div>
                        <div class="btn-group pull-right">
                            <button type="button" id="refresh" class="btn btn-default btn-sm fa fa-fw fa-refresh"
                                    title="Reset sorting" value="" style="width: 40px"></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        @if(Session::has('flash_message'))
            <div class="alert {{ Session::get('flash_type') }}">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                {{ Session::get('flash_message') }}
            </div>
        @endif

        <div class="row">
            <div class="col-xs-12">
                    <table cellpadding="0" cellspacing="0" border="0"
                           class="table table-responsive table-bordered table-striped table-hover table-condensed dfe-table-user"
                           id="userTable" style="table-layout: fixed; width: 100%;">
                        <thead style="width:100%">
                            <tr>
                                <th style="max-width:100px; width:100px;"></th>
                                <th style="max-width:200px; width:200px;">First Name</th>
                                <th style="max-width:200px; width:200px;">Last Name</th>
                                <th>Email</th>
                                <th style="max-width:175px; width:175px;">Role</th>
                                <th style="max-width:100px; width:100px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6"><i class="fa fa-spinner fa-spin" style="font-size:24px"></i></td>
                            </tr>
                        </tbody>
                    </table>
                <br><br><br><br>
            </div>
            <br><br><br><br>
        </div>
    </div>
    <div style="display:none;" class="templates">
        <form method="POST" action="/v1/users/1" id="single_delete_1_" class="user_frm_template">
            <input type="hidden" id="user_id" name="user_id" value="1">
            <input type="hidden" id="user_type" name="user_type" value=""/>
            <input type="hidden" id="user_name" name="user_name" value=""/>
            <input name="_method" type="hidden" value="DELETE">
            <input name="_token" type="hidden" value="<?= csrf_token(); ?>">
            <input type="hidden" id="edit_url" name="edit_url" value="" />

            <input class="user_checkbox" type="checkbox" value="" id="user_checkbox_1" name="DreamFactory Admin">&nbsp;&nbsp;
            <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash remove_user" value="delete" style="width: 25px;"></button>
        </form>
    </div>
    <script type="text/javascript" src="/js/blade-scripts/common.js"></script>
    <script type="text/javascript" src="/js/blade-scripts/users/users.js"></script>
    <script type="text/javascript" src="/static/plugins/bartaz/jquery.highlight.js"></script>

    <script>


    </script>
@stop

