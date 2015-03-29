<?php
/**
 * @var OAuthController $this
 * @var string          $client_id
 * @var string          $client_secret
 */
$this->pageTitle = 'Your Keys';
?>
<form method="post" action="/oauth/authorize/">
	<input type="hidden" name="client_id" value="nO6XzW8VuyHEXm02SdtMOEeVaaKlSzLu" />
	<input type="hidden" name="client_secret" value="05a984a8a76d215c010e4f895a6f405cbfd4cee30a1119023f7268b3fd5836c4" />
	<legend>Your Access Keys</legend>
	<div class="container">
		<div class="control-group">
			<label>Client ID:</label>
			<div class="controls">
				<span class="input-xlarge uneditable-input"><?php echo $client_id;?></span>
			</div>
		</div>
		<div class="control-group">
			<label>Client Secret:</label>
			<div class="controls">
				<span class="input-xxlarge uneditable-input"><?php echo $client_secret;?></span>
			</div>
		</div>
		<button type="submit" class="btn btn-primary">Authorize</button>
		<button type="reset" class="btn btn-secondary">Cancel</button>
	</div>
</form>