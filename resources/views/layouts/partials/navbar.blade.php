<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container-fluid">

        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#sidebar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/"><img src="/img/logo-dreamfactory-inverse.png" alt=""></a>
        </div>

        <div id="dfe-navbar-collapse" class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle user-info" data-toggle="dropdown">
                        <div class="dropdown avatar img-circle ">
                            <div class="avatar-image" data-hash="{{ '$_user["hash"]' }}"></div>
                        </div>
                        <div class="user-mini pull-right">
                            <span>{!! 'sandman@dreamfactory.com' !!}}</span>
                            <span class="caret"></span>
                        </div>
                    </a>

                    <ul class="dropdown-menu">
                        <li>
                            <a href="#"><i class="fa fa-fw fa-user"></i> Profile</a>
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-fw fa-envelope"></i> Inbox</a>
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-fw fa-gear"></i> Settings</a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#"><i class="fa fa-fw fa-power-off"></i> Log Out</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <form class="navbar-form navbar-right hidden">
                <input type="text" class="form-control" placeholder="Search...">
            </form>
        </div>
    </div>
</nav>