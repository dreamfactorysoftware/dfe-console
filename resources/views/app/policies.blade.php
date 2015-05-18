@include('layouts.partials.topmenu',array('pageName' => 'Policies', 'prefix' => $prefix))

@extends('layouts.main')

@section('content')

    <style>
        tr { height: 20px; }

    </style>
    <div class="col-md-2 df-sidebar-nav">
        <div class="">
            <ul class="nav nav-pills nav-stacked visible-md visible-lg">
                <li class="active">
                    <a class="" href="/{{$prefix}}/policies">Manage</a>
                </li>
                <li class="">
                    <a class="" href="/{{$prefix}}/policies/create">Create</a>
                </li>
            </ul>
        </div>
    </div>

    <div style="" class="col-md-10">


        <div>
            <div class="">
                <div class="df-section-header df-section-all-round">
                    <h4 class="">Create Policies</h4>
                </div>
            </div>
        </div>

        <!-- Tool Bar -->
        <div class="row">
            <form method="POST" action="/{{$prefix}}/policies/multi" id="multi_delete">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">
                <input name="_selected" id="_selected" type="hidden" value="">
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
                            <button type="button" id="selectedServersRemove" class="btn btn-default btn-sm fa fa-fw fa-trash" title="Delete selected servers" value="delete" style="width: 40px"></button>
                        </div>
                        <div style="clear: both"></div>
                    </div>
                </div>
            </form>
        </div>

        <div class="">
            <div class="row">
                <div class="col-xs-12">
                    <table id="policyTable" class="table table-responsive table-bordered table-striped table-hover table-condensed">
                        <thead>
                        <tr>
                            <!--th></th-->
                            <th style="text-align: center; vertical-align: middle;"> </th>
                            <th class="" >
                                Name
                            </th>
                            <th class="" style="text-align: center; vertical-align: middle;">
                                Default
                            </th>
                            <th class="" style="">
                                Status
                            </th>
                        </tr>

                        </thead>
                        <tbody>


                            <tr style="max-height: 20px; height: 20px">
                                <td style="text-align: center; vertical-align: middle;" id="actionColumn" class="form-inline">
                                    <form >
                                            <input type="checkbox" value="" disabled id="server_checkbox_">&nbsp;&nbsp;
                                            <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" disabled value="delete" style="width: 25px" ></button>
                                    </form>
                                </td>

                                <td style="text-align: left; vertical-align: middle;">X-Large</td>
                                <td style="text-align: left; vertical-align: middle;">No</td>
                                <td style="text-align: left; vertical-align: middle;"><span class="label label-success">Active</span></td>
                            </tr>

                            <tr>
                                <td style="text-align: center; vertical-align: middle;" id="actionColumn" class="form-inline">
                                    <form >
                                        <input type="checkbox" value="" disabled id="server_checkbox_">&nbsp;&nbsp;
                                        <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" disabled value="delete" style="width: 25px" ></button>
                                    </form>
                                </td>

                                <td style="text-align: left; vertical-align: middle;">Large</td>
                                <td style="text-align: left; vertical-align: middle;">No</td>
                                <td style="text-align: left; vertical-align: middle;"><span class="label label-success">Active</span></td>
                            </tr>

                            <tr>
                                <td style="text-align: center; vertical-align: middle;" id="actionColumn" class="form-inline">
                                    <form >
                                        <input type="checkbox" value="" disabled id="server_checkbox_">&nbsp;&nbsp;
                                        <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" disabled value="delete" style="width: 25px" ></button>
                                    </form>
                                </td>

                                <td style="text-align: left; vertical-align: middle;">Medium</td>
                                <td style="text-align: left; vertical-align: middle;">Yes</td>
                                <td style="text-align: left; vertical-align: middle;"><span class="label label-success">Active</span></td>
                            </tr>

                            <tr>
                                <td style="text-align: center; vertical-align: middle;" id="actionColumn" class="form-inline">
                                    <form >
                                        <input type="checkbox" value="" disabled id="server_checkbox_">&nbsp;&nbsp;
                                        <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" disabled value="delete" style="width: 25px" ></button>
                                    </form>
                                </td>

                                <td style="text-align: left; vertical-align: middle;">Small</td>
                                <td style="text-align: left; vertical-align: middle;">No</td>
                                <td style="text-align: left; vertical-align: middle;"><span class="label label-success">Active</span></td>
                            </tr>

                            <tr>
                                <td style="text-align: center; vertical-align: middle;" id="actionColumn" class="form-inline">
                                    <form >
                                        <input type="checkbox" value="" disabled id="server_checkbox_">&nbsp;&nbsp;
                                        <button type="button" class="btn btn-default btn-xs fa fa-fw fa-trash" disabled value="delete" style="width: 25px" ></button>
                                    </form>
                                </td>

                                <td style="text-align: left; vertical-align: middle;">Micro</td>
                                <td style="text-align: left; vertical-align: middle;">No</td>
                                <td style="text-align: left; vertical-align: middle;"><span class="label label-warning">Not Active</span></td>
                            </tr>

                        </tbody>
                    </table>
                    <span id="tableInfo"></span>
                    <br><br><br><br>
                </div>
            </div>
        </div>
        </form>
    </div>





@stop