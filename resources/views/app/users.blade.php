@include('layouts.partials.topmenu',array('pageName' => 'Users', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')
    <div class="col-md-2">
        <div >
            <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                <li class="active">
                    <a href="/{{$prefix}}/users">Manage</a>
                </li>
                <li>
                    <a  href="/{{$prefix}}/users/create">Create</a>
                </li>
            </ul>
        </div>
    </div>

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

                        <button type="button" disabled="true" class="btn btn-default btn-sm fa fa-fw fa-backward" id="_prev" style="width: 40px"></button>

                        <div class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown" aria-expanded="false">
                                <span id="currentPage">Page 1</span> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu" id="tablePages">
                            </ul>
                        </div>

                        <button type="button" disabled="true" class="btn btn-default btn-sm fa fa-fw fa-forward" id="_next" style="width: 40px"></button>
                    </div>
                    <div class="btn-group">
                        <button type="button" id="selectedUsersRemove" class="btn btn-default btn-sm fa fa-fw fa-trash" title="Delete selected clusters" value="delete" style="width: 40px"></button>
                    </div>
                    <div class="btn-group">
                        <input id="userSearch" class="form-control input-sm" value="" type="text" placeholder="Search Users...">
                    </div>
                </div>
            </div>
            </form>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <table cellpadding="0" cellspacing="0" border="0" class="table table-responsive table-bordered table-striped table-hover table-condensed dfe-table-user" id="userTable">
                    <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>Name</th>
                        <th>Nickname</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $key => $value)
                        <tr>
                            <td>
                                <input type="hidden" id="user_id" value="{{ $value->id }}">
                                <input type="hidden" id="user_type" value="{{ $value->admin }}">
                            </td>
                            <td style="height: 25px" id="actionColumn">
                                <form method="POST" action="/{{$prefix}}/users/{{$value->id}}" id="single_delete_{{ $value->id }}_{{ $value->admin }}">
                                    <input type="hidden" id="user_id" name="user_id" value="{{ $value->id }}">
                                    <input type="hidden" id="user_type" name="user_type" value="{{ $value->admin }}">
                                    <input name="_method" type="hidden" value="DELETE">
                                    <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">

                                    @if( Auth::user()->id != $value->id )
                                        <input type="checkbox" value="{{ $value->id }},{{ $value->admin }}" id="user_checkbox_{{ $value->id }}">&nbsp;&nbsp;
                                        <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" onclick="removeUser({{ $value->id }}, '{{ $value->first_name_text }} {{ $value->last_name_text }}', '{{ $value->admin }}')" value="delete" style="width: 25px" ></button>
                                    @endif
                                </form>
                            </td>
                            <td>{{ $value->first_name_text }} {{ $value->last_name_text }}</td>
                            <td>{{ $value->nickname_text }}</td>
                            <td>{{ $value->email_addr_text }}</td>

                            @if($value->admin == 0)
                                <td><span class="label label-info" id="user_type">DSP Owner</span></td>
                            @else
                                <td><span class="label label-primary" id="user_type">System Administrator</span></td>
                            @endif

                            @if($value->active_ind == 0)
                                <td><span class="label label-warning">Not Active</span></td>
                            @else
                                <td><span class="label label-success">Active</span></td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <span id="tableInfo"></span>
                <br><br><br><br>
            </div>
            <br><br><br><br>
        </div>
    </div>

    <script type="text/javascript" src="../../../js/blade-scripts/users/users.js"></script>
@stop

