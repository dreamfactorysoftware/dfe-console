@if (\Auth::check())
    <div id="top-bar-mask"></div>
    <nav class="navbar navbar-default navbar-fixed-top df-top-navbar" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand"> <img src="/img/DreamFactory-logo-inverse-horiz.png" alt="DreamFactory Enterprise"> </a>
            </div>
            <div class="collapse navbar-collapse" id="navbar-collapse-1">
                <ul class="nav navbar-nav navbar-right df-navbar-nav">
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"> <i class="fa fa-fw fa-user"></i>
                            <span>{{ Auth::user()->email_addr_text }}</span>
                            <span class="caret"></span>
                        </a>

                        <ul class="dropdown-menu">
                            <li>
                                <a href="/v1/users/{{ Auth::user()->id }}/edit?user_type=admin"><i class="fa fa-fw fa-user"></i> Profile</a>
                            </li>
                            <!--li>
                                <a href="/"><i class="fa fa-fw fa-envelope"></i> Inbox</a>
                            </li>
                            <li>
                                <a href="/"><i class="fa fa-fw fa-gear"></i> Settings</a>
                            </li-->
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
@endif
