@if (\Auth::check())
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <div class="container-fluid">
            <nav class="navbar navbar-default">
                <div class="container-fluid">
                    <!-- Brand and toggle get grouped for better mobile display -->
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#sidebar-console">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand" href="/"><img src="/img/logo-dreamfactory-inverse.png" alt=""></a>
                    </div>

                    <div class="collapse navbar-collapse navbar-right" id="sidebar-console">
                        <ul class="nav navbar-nav">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle user-info" data-toggle="dropdown">
                                    <div class="dropdown avatar img-circle ">
                                        <div class="avatar-image" data-hash="{{ md5(\Auth::user()->email_addr_text) }}"></div>
                                    </div>
                                    <div class="user-mini pull-right">
                                        <span>{!! \Auth::user()->email_addr_text !!}</span>
                                        <span class="caret"></span>
                                    </div>
                                </a>

                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="/user/profile"><i class="fa fa-fw fa-user"></i> Profile</a>
                                    </li>
                                    <li>
                                        <a href="/console/inbox"><i class="fa fa-fw fa-envelope"></i> Inbox</a>
                                    </li>
                                    <li>
                                        <a href="/console/settings"><i class="fa fa-fw fa-gear"></i> Settings</a>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <a href="/auth/logout"><i class="fa fa-fw fa-power-off"></i> Log Out</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </nav>
@endif
