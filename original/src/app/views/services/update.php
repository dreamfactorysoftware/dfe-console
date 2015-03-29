<?php
if ( !isset( $update ) )
{
	$update = false;
}

if ( $update )
{
	$_options = array(
		'subtitle'    => 'Update a Service',
		'header'      => 'Update Service "' . $model->service_name_text . '"',
		'subHeader'   => '<p style="margin-top:0;padding-top:0;">Enter your changes below and press the "Save" button. Your changes <em>should</em> be saved.</p>',
		'breadcrumbs' => array(
			'Home'                    => '/',
			'Service Manager'         => '/services/admin/',
			$model->service_name_text => false,
		),
		'menu'        => array(
			'Service Manager'  => array( 'admin' ),
			'Create a Service' => array( 'create' ),
		),
	);
}
else
{
	$_options = array(
		'header'      => 'Create a Service',
		'subHeader'   => '<p style="margin-top:0;padding-top:0;">Enter your the details of the service you wish to create below and press the "Save" button. Your changes <em>should</em> be saved.</p>',
		'breadcrumbs' => array(
			'Home'                    => '/',
			'Service Manager'         => '/services/admin/',
			'New Service'             => false,
		),
		'menu'        => array(
			'Service Manager'  => array( 'admin' ),
		),
	);
}

$_options['uiStyle'] = \PS::UI_BOOTSTRAP;

//	Render the form
echo $this->renderPartial(
	'_form',
	array(
		'model'        => $model,
		'_formOptions' => $this->setStandardFormOptions( $model, $_options ),
		'update'       => $update,
	)
);
