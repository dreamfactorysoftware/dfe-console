<?php
/**
 * @var ProfileController $this
 */
?>
<div class="container service-container">
	<legend>Services In Use</legend>
<?php
$_services = ServiceUserMap::model()->findAll(
	'user_id = :user_id',
	array(
		':user_id' => \Yii::app()->user->getId(),
	)
);

if ( !empty( $_services ) )
{
	echo '<ul class="available-services">';

	foreach ( $_services as $_service )
	{
		$_imageUrl = $_service->icon_url_text ? : '/img/logo-service-unknown-128x128.png';

		echo <<<HTML
<li><a href="#" title="{$_service->service_name_text}"><img src="{$_service->icon_url_text}" class="service-logo"></a></li>
HTML;
	}

	echo '</ul>';
}
else
{
	echo <<<HTML
<h4>No services configured.</h4>
HTML;
}

echo '</div >';

$_mirror = new \ReflectionClass( '\\DreamFactory\\Interfaces\\ServiceClass' );

foreach ( $_mirror->getConstants() as $_name => $_index )
{
	if ( false === strpos( $_name, 'Service_' ) )
	{
		continue;
	}

	$_tag = ucwords( str_ireplace( 'Service_', null, $_name ) );
	\Kisma\Core\Utility\Log::info( 'Found service "' . $_tag . '"' );

	$_services = Service::model()->findAll(
		'public_ind = :public_ind and service_class_nbr = :service_class_nbr',
		array(
			':public_ind'        => 1,
			':service_class_nbr' => $_index,
		)
	);

	$_items = '<h4>No services available.</h4>';

	if ( !empty( $_services ) )
	{
		$_items = null;

		foreach ( $_services as $_service )
		{
			$_items .= <<<HTML
<li><a href="/rest/{$_service->service_tag_text}/" title="{$_service->service_name_text}"><img src="{$_service->icon_url_text}" class="service-logo"></a></li>
HTML;
		}

		$_items = '<ul class="available-services">' . $_items . '</ul>';
	}

	echo <<<HTML
<div class="container service-container">
	<legend>{$_tag} Services</legend>
	{$_items}
</div>
HTML;
}