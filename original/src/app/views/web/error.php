<?php
/**
 * @var array $error
 */
?>
<div class="row-fluid">
	<div class="span12">
		<div class="widget-box">
			<div class="widget-title"><span class="icon"> <i class="icon-info-sign"></i> </span>
				<h5>Error <?php echo $error['code']; ?></h5>
			</div>
			<div class="widget-content">
				<div class="error_ex">
					<h1><?php echo $error['code']; ?></h1>

					<h2>D'oh!</h2>

					<p><?php echo $error['message'];?></p>
					<a class="btn btn-warning btn-big" href="/">Back to Home</a></div>
			</div>
		</div>
	</div>
</div>
