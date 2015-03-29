<?php
/**
 * _menu_services.php
 * Partial menu view
 */
?>
<fieldset>
	<legend>Services</legend>

	<div class="menu-icon">
		<img src="<?php echo \DreamFactory\Yii\Utility\Pii::url( '/img/icon-services.png' ); ?>" />
	</div>

	<div class="menu-items">
		<ul class="menu-items ui-widget">
			<li class="menu-item">
				<a href="<?php echo \DreamFactory\Yii\Utility\Pii::url( '/services/create' );?>">Create a Service</a>

				<p class="info">Creates a new service in the system.</p>
			</li>
			<li class="menu-item">
				<a href="<?php echo \DreamFactory\Yii\Utility\Pii::url( '/services/admin' );?>">Service Manager</a>

				<p class="info">List and manage all services.</p>
			</li>
			<li class="menu-item">
				<a href="<?php echo \DreamFactory\Yii\Utility\Pii::url( '/profile/index/' );?>">Add a Service</a>

				<p class="info">Add a new service to your account.</p>
			</li>
		</ul>
	</div>

	<fieldset class="status-menu">
		<legend>Service Status</legend>
		<div class="menu-items">
			<ul class="menu-items ui-widget">
				<?php
	//				foreach ( Service::model()->findAll() as $_service )
	//				{
	//					$_statusColor = 'green';
	//
	//					if ( 0 == $_service->enable_ind )
	//					{
	//						$_statusColor = 'red';
	//					}
	//
	//					?>
	<!--					<li class="menu-item service-status---><?php //echo $_statusColor; ?><!--" title="Status: --><?php //echo $_statusColor; ?><!--">-->
	<!--						<a href="--><?php //echo \DreamFactory\Yii\Utility\Pii::url( 'rest/' . $_service->service_tag_text . '/' ); ?><!--">--><?php //echo $_service->service_name_text; ?><!--</a>-->
	<!--					</li>-->
	<!--					--><?php
	//				}
				?>
			</ul>
		</div>
	</fieldset>
</fieldset>
