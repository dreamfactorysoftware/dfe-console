<?php
/**
 * @var string        $content
 * @var WebController $this
 */
use DreamFactory\Yii\Utility\Pii;
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Cerberus | Dashboard</title>
	<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="author" content="DreamFactory Software, Inc.">
	<meta name="language" content="en" />
	<link rel="shortcut icon" href="//www.dreamfactory.com/favicon.ico" />
	<link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.no-icons.min.css" rel="stylesheet">
	<link href="//netdna.bootstrapcdn.com/font-awesome/3.0.2/css/font-awesome.css" rel="stylesheet">
	<!--[if IE 7]>
	<link href="http://netdna.bootstrapcdn.com/font-awesome/3.0.2/css/font-awesome-ie7.css" rel="stylesheet">    <![endif]-->
	<link rel="stylesheet" href="/css/main.css" />
	<link rel="stylesheet" href="/css/media.css" />
	<link rel="stylesheet" href="/css/jquery.gritter.css" />
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
	<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->    <!--[if lt IE 9]>
	<script type="text/javascript" src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>    <![endif]-->
	<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
</head>
<body>
<!--Header-part-->
<div id="header">
	<h1><a href="/"></a></h1>
</div>
<!--close-Header-part-->

<!--top-Header-menu-->
<div id="user-nav" class="navbar navbar-inverse">
	<ul class="nav">
		<li class="dropdown" id="profile-messages"><a title="" href="#" data-toggle="dropdown" data-target="#profile-messages"
													  class="dropdown-toggle"><i class="icon icon-user"></i> <span class="text"><?php echo Pii::user(
					)->name; ?></span><b
					class="caret"></b></a>

			<ul class="dropdown-menu">
				<li><a href="/app/profile"><i class="icon-user"></i> Profile</a></li>
				<li class="divider"></li>
				<li><a href="/app/tasks"><i class="icon-check"></i> Tasks</a></li>
				<li class="divider"></li>
				<li><a href="/app/logout"><i class="icon-key"></i> Log Out</a></li>
			</ul>
		</li>
		<li class="dropdown" id="menu-messages"><a href="#" data-toggle="dropdown" data-target="#menu-messages" class="dropdown-toggle"><i
					class="icon icon-envelope"></i> <span class="text">Messages</span> <span class="label label-important">5</span> <b
					class="caret"></b></a>

			<ul class="dropdown-menu">
				<li><a class="sAdd" title="" href="#"><i class="icon-plus"></i> new message</a></li>
				<li class="divider"></li>
				<li><a class="sInbox" title="" href="#"><i class="icon-envelope"></i> inbox</a></li>
				<li class="divider"></li>
				<li><a class="sOutbox" title="" href="#"><i class="icon-arrow-up"></i> outbox</a></li>
				<li class="divider"></li>
				<li><a class="sTrash" title="" href="#"><i class="icon-trash"></i> trash</a></li>
			</ul>
		</li>
		<li class=""><a title="" href="#"><i class="icon icon-cog"></i> <span class="text">Settings</span></a></li>
		<li class=""><a title="" href="/app/logout"><i class="icon icon-share-alt"></i> <span class="text">Logout</span></a></li>
	</ul>
</div>
<!--close-top-Header-menu--><!--start-top-search-->
<div id="search">
	<input type="text" placeholder="Search here..." />
	<button type="submit" class="tip-bottom" title="Search"><i class="icon-search icon-white"></i></button>
</div>
<!--close-top-search--><!--sidebar-menu-->
<div id="sidebar"><a href="#" class="visible-phone"><i class="icon icon-home"></i> Dashboard</a>

	<ul>
		<li class="active"><a href="/"><i class="icon icon-home"></i> <span>Dashboard</span></a></li>
		<li><a href="/app/charts"><i class="icon icon-signal"></i> <span>Charts &amp; graphs</span></a></li>
		<li><a href="/app/widgets"><i class="icon icon-inbox"></i> <span>Widgets</span></a></li>
		<li><a href="/app/tables"><i class="icon icon-th"></i> <span>Tables</span></a></li>
		<li><a href="/app/full"><i class="icon icon-fullscreen"></i> <span>Full width</span></a></li>
		<li class="submenu"><a href="#"><i class="icon icon-th-list"></i> <span>Forms</span> <span class="label label-important">3</span></a>

			<ul>
				<li><a href="form-common.html">Basic Form</a></li>
				<li><a href="form-validation.html">Form with Validation</a></li>
				<li><a href="form-wizard.html">Form with Wizard</a></li>
			</ul>
		</li>
		<li><a href="buttons.html"><i class="icon icon-tint"></i> <span>Buttons &amp; icons</span></a></li>
		<li><a href="interface.html"><i class="icon icon-pencil"></i> <span>Eelements</span></a></li>
		<li class="submenu"><a href="#"><i class="icon icon-file"></i> <span>Addons</span> <span class="label label-important">5</span></a>

			<ul>
				<li><a href="index2.html">Dashboard2</a></li>
				<li><a href="gallery.html">Gallery</a></li>
				<li><a href="calendar.html">Calendar</a></li>
				<li><a href="invoice.html">Invoice</a></li>
				<li><a href="chat.html">Chat option</a></li>
			</ul>
		</li>
		<li class="submenu"><a href="#"><i class="icon icon-info-sign"></i> <span>Error</span> <span class="label label-important">4</span></a>

			<ul>
				<li><a href="error403.html">Error 403</a></li>
				<li><a href="error404.html">Error 404</a></li>
				<li><a href="error405.html">Error 405</a></li>
				<li><a href="error500.html">Error 500</a></li>
			</ul>
		</li>
		<li class="content"><span>Monthly API Calls</span>

			<div class="progress progress-mini progress-danger active progress-striped">
				<div class="bar"></div>
			</div>
			<span class="percent"></span>

			<div class="stat"></div>
		</li>
		<li id="disk_usage" class="content"><span>Disk Space Usage</span>

			<div class="progress progress-mini active progress-striped">
				<div class="bar"></div>
			</div>
			<span class="percent"></span>

			<div class="stat"></div>
		</li>
	</ul>
</div>
<!--sidebar-menu-->

<!--main-container-part-->
<div id="content">
	<!--breadcrumbs-->
	<div id="content-header">
		<div id="breadcrumb"><a href="/" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> Home</a></div>
	</div>
	<!--End-breadcrumbs-->

	<!--Action boxes-->
	<div class="container-fluid">
		<?php echo $content; ?>
	</div>
</div>

<!--end-main-container-part-->

<!--Footer-part-->

<div class="row-fluid">
	<div id="footer" class="span12"> 2012-<?php echo date( 'Y' ); ?> &copy; DreamFactory Software, Inc. All Rights Reserved.</div>
</div>

<!--end-Footer-part-->

<script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>
<script src="/js/jquery.gritter.min.js"></script>
<!--<script src="/js/jquery.validate.js"></script>-->
<!--<script src="/js/jquery.wizard.js"></script>-->
<!--<script src="/js/jquery.uniform.js"></script>-->
<!--<script src="/js/select2.min.js"></script>-->
<!--<script src="/vendor/datatables/js/jquery.dataTables.js"></script>-->
<script src="/js/matrix.js"></script>
<script src="/js/cerberus.dashboard.js"></script>

<script type="text/javascript">
// This function is called from the pop-up menus to transfer to
// a different page. Ignore if the value returned is a null string:
function goPage(newURL) {

	// if url is empty, skip the menu dividers and reset the menu selection to default
	if (newURL != "") {

		// if url is "-", it is this page -- reset the menu:
		if (newURL == "-") {
			resetMenu();
		}
		// else, send page to designated URL
		else {
			document.location.href = newURL;
		}
	}
}

// resets the menu selection upon entry to this page:
function resetMenu() {
	document.gomenu.selector.selectedIndex = 2;
}
</script>
</body>
</html>
