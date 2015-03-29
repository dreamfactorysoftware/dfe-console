<?php
/**
 * @var $this WebController
 */

use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Utility\Sql;

Sql::setConnection( Pii::pdo() );

?>
<div class="quick-actions_homepage">
	<ul class="quick-actions">
		<!--		<li class="bg_lb"><a href="/"> <i class="icon-dashboard"></i> <span class="label label-important">20</span> My Dashboard </a></li>-->
		<!--		<li class="bg_lg span3"><a href="charts.html"> <i class="icon-signal"></i> Charts</a></li>-->
		<!--		<li class="bg_ly"><a href="widgets.html"> <i class="icon-inbox"></i><span class="label label-success">101</span> Widgets </a></li>-->
		<!--		<li class="bg_lo"><a href="tables.html"> <i class="icon-th"></i> Tables</a></li>-->
		<!--		<li class="bg_ls"><a href="grid.html"> <i class="icon-fullscreen"></i> Full width</a></li>-->
		<!--		<li class="bg_lo span3"><a href="form-common.html"> <i class="icon-th-list"></i> Forms</a></li>-->
		<!--		<li class="bg_ls"><a href="buttons.html"> <i class="icon-tint"></i> Buttons</a></li>-->
		<!--		<li class="bg_lb"><a href="interface.html"> <i class="icon-pencil"></i>Elements</a></li>-->
		<!--		<li class="bg_lg"><a href="calendar.html"> <i class="icon-calendar"></i> Calendar</a></li>-->
		<!--		<li class="bg_lr"><a href="error404.html"> <i class="icon-info-sign"></i> Error</a></li>-->
	</ul>
</div><!--End-Action boxes-->
<!--Chart-box-->
<div class="row-fluid">
	<div class="widget-box">
		<div class="widget-title bg_lg"><span class="icon"><i class="icon-signal"></i></span>
			<h5>API Calls (hosted DSPs only, calls per day)</h5>
		</div>
		<div class="widget-content">
			<div class="row-fluid">
				<div class="span9">
					<div class="chart" id="timeline-chart"></div>
				</div>
				<div class="span3">
					<ul class="site-stats">
						<li class="bg_lg"><i class="icon-sitemap"></i><strong><span id="db_dsp_count_live"></span></strong>
							<small>Activated DSPs</small>
						</li>
						<li class="bg_lo"><i class="icon-ambulance"></i><strong><span id="db_dsp_count_dead"></span></strong>
							<small>Non-Activated DSPs</small>
						</li>
						<li class="bg_dy"><i class="icon-user"></i><strong><span id="db_user_count"></span></strong>
							<small>Total DSP Users</small>
						</li>
						<li class="bg_lh"><i class="icon-beaker"></i><strong><span id="db_dsp_database_tables"></span></strong>
							<small>Total Database Tables</small>
						</li>
						<li class="bg_lh"><i class="icon-sitemap"></i><strong><span id="db_dsp_apps"></span></strong>
							<small>Total Apps (non-system)</small>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row-fluid">
	<div class="span6">
		<div class="widget-box">
			<div class="widget-title bg_lo" data-toggle="collapse" href="#collapse-tc-logins"><span class="icon"><i class="icon-chevron-down"></i></span>
				<h5>DSP User Logins</h5>
			</div>
			<div class="widget-content nopadding in collapse" id="collapse-tc-logins" style="height: auto;">
				<div class="chart" id="timeline-chart-logins"></div>
			</div>
		</div>
		<div class="widget-box">
			<div class="widget-title bg_lo" data-toggle="collapse" href="#collapse-tc-activations"><span class="icon"><i class="icon-chevron-down"></i></span>
				<h5>DSP Activations</h5>
			</div>
			<div class="widget-content nopadding in collapse" id="collapse-tc-activations" style="height: auto;">
				<div class="chart" id="timeline-chart-activations"></div>
			</div>
		</div>
	</div>
	<div class="span6">
		<div class="widget-box">
			<div class="widget-title bg_lo" data-toggle="collapse" href="#collapse-tc-provision"><span class="icon"><i class="icon-chevron-down"></i></span>
				<h5>Provision Requests</h5>
			</div>
			<div class="widget-content nopadding in collapse" id="collapse-tc-provision" style="height: auto;">
				<div class="chart" id="timeline-chart-provision"></div>
			</div>
		</div>
		<div class="widget-box">
			<div class="widget-title bg_lo" data-toggle="collapse" href="#collapse-tc-fabric-api"><span class="icon"><i class="icon-chevron-down"></i></span>
				<h5>Fabric API</h5>
			</div>
			<div class="widget-content nopadding in collapse" id="collapse-tc-fabric-api" style="height: auto;">
				<div class="chart" id="timeline-chart-fabric-api"></div>
			</div>
		</div>
	</div>
</div>
<hr /><!--End-Chart-box-->
<div class="row-fluid">
	<div class="span6"></div>
	<div class="span6"></div>
</div>
<script src="//code.highcharts.com/highcharts.js"></script>
<script src="//code.highcharts.com/modules/exporting.js"></script>