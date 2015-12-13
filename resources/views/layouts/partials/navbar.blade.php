@if (\Auth::check())
	<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
		<div class="container-fluid">
			<div class="navbar-header">
				<a><img src="{{config('dfe.common.navbar-image')}}" class="dfe-topnav-logo-height"></a>
			</div>
			<div class="collapse navbar-collapse">
				<ul class="nav navbar-nav navbar-right">
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"> <i class="fa fa-fw fa-user"></i>
							<span>{{ Auth::user()->nickname_text }}</span> <span class="caret"></span> </a>

						<ul class="dropdown-menu">
							<li>
								<a href="/v1/users/{{ Auth::user()->id }}/edit?user_type=admin"><i
										class="fa fa-fw fa-user"></i>Profile</a>
							</li>
							<li class="divider"></li>
							<li>
								<!--suppress HtmlUnknownTarget -->
								<a href="/auth/logout"><i class="fa fa-fw fa-power-off"></i>Log Out</a>
							</li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
	</nav>
@endif
