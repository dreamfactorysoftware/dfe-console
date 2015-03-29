<?php
/**
 * @var $this       OAuthController
 * @var $authParams array
 */
$this->pageTitle = 'Authorize';
?>
<form method="POST" action="/oauth/authorize/">
	<?php
	foreach ( $authParams as $_key => $_value )
	{
		$_key = htmlspecialchars( $_key, ENT_QUOTES );
		$_value = htmlspecialchars( $_value, ENT_QUOTES );

		echo <<<HTML
<input type="hidden" name="{$_key}" value="{$_value}" />
HTML;
	}
	?>

	Do you authorize the app to do its thing?
	<p>
		<input type="submit" name="accept" value="Yep" />
		<input type="reset" name="cancel" value="NFW!" />
	</p>
</form>
