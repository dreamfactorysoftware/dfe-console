@if (\Auth::check())
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <a><img src="{{config('dfe.common.navbar-image')}}" class="dfe-topnav-logo-height"></a>
            </div>

            <div class="collapse navbar-collapse navbar-right" id="navbar-dashboard">
                <ul class="nav navbar-nav">
                    <li><a href="http://www.dreamfactory.com/resources/"><i class="fa fa-fw fa-book"></i>Resources</a>
                    </li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-fw fa-user"></i>
                            <span>{{ \Auth::user()->nickname_text ?: \Auth::user()->first_name_text }}</span>
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="/v1/users/{{ Auth::user()->id }}/edit?user_type=admin"><i
                                            class="fa fa-fw fa-user"></i>Profile</a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="/auth/logout"><i class="fa fa-fw fa-sign-out"></i>Logout</a>
                            </li>
                            <li>
                                <a href="logout"><i class="fa fa-fw fa-sign-out"></i>Logout2</a>
                            </li>

                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
@endif
