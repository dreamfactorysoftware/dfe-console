@include('layouts.partials.topmenu',array('pageName' => 'Policies', 'prefix' => $prefix))

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
                                            <li class="">
                                                <a class="" href="/{{$prefix}}/policies">Manage</a>
                                            </li>
                                            <li class="active">
                                                <a class="" href="/{{$prefix}}/policies/create">Create</a>
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
                                            <df-section-header class="" data-title="'Manage Servers'">
                                                <div class="nav nav-pills dfe-section-header">
                                                    <h4 class="">Create Policy</h4>
                                                </div>
                                            </df-section-header>

                                            <form method="POST" action="/{{$prefix}}/policies">
                                                <input name="_method" type="hidden" value="POST">
                                                <input name="_token" type="hidden" value="<?php echo csrf_token(); ?>">

                                                <!--form class="" name="create-user"-->
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Name</label>
                                                            <input id="server_id_text" name="server_id_text" class="form-control" placeholder="Enter server name." type="name" required>
                                                        </div>

                                                        <div class="form-group">
                                                            <label>Description</label>
                                                            <textarea class="form-control" rows="3" id="description_text" placeholder="Enter policy description." required></textarea>
                                                        </div>

                                                        <div class="row">
                                                            <br>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-2">
                                                                Default
                                                            </div>
                                                            <div class="col-md-2">
                                                                <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text">
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <br>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-2">
                                                                Active
                                                            </div>
                                                            <div class="col-md-2">
                                                                <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text">
                                                            </div>
                                                        </div>


                                                    </div>



                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Per Instance Limits</label>
                                                            <div role="tabpanel">

                                                                <!-- Nav tabs -->
                                                                <ul class="nav nav-tabs" role="tablist">
                                                                    <li role="presentation" class="active"><a href="#instance_limit_min" aria-controls="home" role="tab" data-toggle="tab">Minute</a></li>
                                                                    <li role="presentation"><a href="#instance_limit_hour" aria-controls="profile" role="tab" data-toggle="tab">Hour</a></li>
                                                                    <li role="presentation"><a href="#instance_limit_day" aria-controls="messages" role="tab" data-toggle="tab">Day</a></li>
                                                                    <li role="presentation"><a href="#instance_limit_7day" aria-controls="settings" role="tab" data-toggle="tab">7 Day</a></li>
                                                                    <li role="presentation"><a href="#instance_limit_30day" aria-controls="settings" role="tab" data-toggle="tab">30 Day</a></li>
                                                                </ul>

                                                                <!-- Tab panes -->
                                                                <div class="tab-content">
                                                                    <div role="tabpanel" class="tab-pane active" id="instance_limit_min">
                                                                        <div class="form-group" style="text-align: center; vertical-align: middle; height: 125px;">
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                            <div class="col-md-2">
                                                                                <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                            </div>
                                                                            <div class="col-md-2">
                                                                                Maximum
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <input type="text" class="form-control" style="width: auto">
                                                                            </div>
                                                                            </div>
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                    <div role="tabpanel" class="tab-pane" id="instance_limit_hour">
                                                                        <div class="form-group" style="text-align: center; vertical-align: middle; height: 125px;">
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div role="tabpanel" class="tab-pane" id="instance_limit_day">
                                                                        <div class="form-group" style="text-align: center; vertical-align: middle; height: 125px;">
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div role="tabpanel" class="tab-pane" id="instance_limit_7day">
                                                                        <div class="form-group" style="text-align: center; vertical-align: middle; height: 125px;">
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div role="tabpanel" class="tab-pane" id="instance_limit_30day">
                                                                        <div class="form-group" style="text-align: center; vertical-align: middle; height: 125px;">
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Per User Limits</label>
                                                            <div role="tabpanel">

                                                                <!-- Nav tabs -->
                                                                <ul class="nav nav-tabs" role="tablist">
                                                                    <li role="presentation" class="active"><a href="#user_limit_min" aria-controls="home" role="tab" data-toggle="tab">Minute</a></li>
                                                                    <li role="presentation"><a href="#user_limit_hour" aria-controls="profile" role="tab" data-toggle="tab">Hour</a></li>
                                                                    <li role="presentation"><a href="#user_limit_day" aria-controls="messages" role="tab" data-toggle="tab">Day</a></li>
                                                                    <li role="presentation"><a href="#user_limit_7day" aria-controls="settings" role="tab" data-toggle="tab">7 Day</a></li>
                                                                    <li role="presentation"><a href="#user_limit_30day" aria-controls="settings" role="tab" data-toggle="tab">30 Day</a></li>
                                                                </ul>

                                                                <!-- Tab panes -->
                                                                <div class="tab-content">
                                                                    <div role="tabpanel" class="tab-pane active" id="user_limit_min">
                                                                        <div class="form-group" style="text-align: center; vertical-align: middle; height: 125px;">
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                    <div role="tabpanel" class="tab-pane" id="user_limit_hour">
                                                                        <div class="form-group" style="text-align: center; vertical-align: middle; height: 125px;">
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div role="tabpanel" class="tab-pane" id="user_limit_day">
                                                                        <div class="form-group" style="text-align: center; vertical-align: middle; height: 125px;">
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div role="tabpanel" class="tab-pane" id="user_limit_7day">
                                                                        <div class="form-group" style="text-align: center; vertical-align: middle; height: 125px;">
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div role="tabpanel" class="tab-pane" id="user_limit_30day">
                                                                        <div class="form-group" style="text-align: center; vertical-align: middle; height: 125px;">
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                            <div><br></div>
                                                                            <div class="row">
                                                                                <div class="col-md-2">
                                                                                    <input id="db_multi_asgn_text" class="" type="checkbox" id="db_multi_asgn_text"></td>
                                                                                </div>
                                                                                <div class="col-md-2">
                                                                                    Maximum
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <input type="text" class="form-control" style="width: auto">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
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
                                                                <button type="button" class="btn btn-primary">Create</button>
                                                                &nbsp;&nbsp;
                                                                <button type="button" class="btn btn-default">
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


@stop