<!--Start Header-->
<!--suppress ALL -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-navbar">
                <span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span>
            </button>
            <div class="navbar-logo">
                <a class="navbar-brand" href="/">DreamFactory Enterprise&trade;</a>
            </div>
        </div>

        <div class="collapse navbar-collapse" id="main-navbar">
            <ul class="nav navbar-nav pull-right">
                <li class="hidden-xs navbar-icon hide">
                    <a href="#" class="modal-link"> <i class="fa fa-bell"></i></a>
                </li>
                <li class="hidden-xs navbar-icon hide">
                    <a href="/app/messages" class="ajax-link"> <i class="fa fa-envelope"></i></a>
                </li>
                <li class="dropdown avatar img-circle ">
                    <div class="avatar-image" data-hash="{{ md5( trim( strtolower( Pii::user()->name ) ) ) }}"></div>
                </li>
                <li class="dropdown user-info">
                    <a href="#" class="dropdown-toggle account" data-toggle="dropdown">{{ Pii::user()->name }}
                        <span class="caret"></span> </a>

                    <ul class="dropdown-menu" role="menu">
                        <li>
                            <a class="ajax-link" href="/app/profile"> <i class="fa fa-user"></i>

                                <span>Profile</span> </a>
                        </li>
                        <li>
                            <a href="/app/messages" class="ajax-link"> <i class="fa fa-envelope"></i>

                                <span>Messages</span> </a>
                        </li>
                        <li>
                            <a class="ajax-link" href="/app/settings"> <i class="fa fa-cog"></i>

                                <span>Settings</span> </a>
                        </li>
                        <li>
                            <a href="/web/logout"> <i class="fa fa-power-off"></i>

                                <span>Logout</span> </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>