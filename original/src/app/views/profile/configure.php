<?php
/**
 * @var ProfileController $this
 * @var \Service          $model
 */
if ( !isset( $update ) )
{
	$update = false;
}

$_options = array(
	'uiStyle'     => \PS::UI_BOOTSTRAP,
	'header'      => 'Configure a Service',
	'breadcrumbs' => array(
		'Home'            => '/',
		'Service Manager' => '/profile/services/',
	),
);

$_options['breadcrumbs'][( $update ? $model->service_name_text : 'New Service' )] = false;

//	Render the form
echo $this->renderPartial(
	'_form',
	array(
		'model'        => $model,
		'_formOptions' => $this->setStandardFormOptions( $model, $_options ),
		'update'       => $update,
	)
);
