<?php
/**
 * Expected variables:
 *
 * @var \DreamFactory\Yii\Models\Forms\DreamLoginForm $model     The form model
 * @var boolean                                       $loginPost True if this load is a post-back
 * @var boolean                                       $success   True if post-back and login success
 * @var \DreamFactory\Yii\Controllers\DreamController $this
 * @var string                                        $header
 *
 * Optional variable:
 * @var string                                        $modelName Defaults to DreamLoginForm
 */
use DreamFactory\Yii\Utility\Validate;

$_message = null;

Validate::register(
	'form#loginform',
	array(
		 'ignoreTitle'    => true,
		 'errorClass'     => 'help-inline',
		 'errorElement'   => 'span',
		 'errorPlacement' => 'function(error,element){error.appendTo(element.parent("div"));}',
		 'rules'          => array(
			 'DreamLoginForm[email_addr_text]' => array(
				 'required' => true,
				 'email'    => true,
			 ),
			 'DreamLoginForm[password_text]'   => array(
				 'required'  => true,
				 'minlength' => 5,
			 ),
		 ),
	)
);

Validate::register(
	'form#recoverform',
	array(
		 'ignoreTitle'    => true,
		 'errorClass'     => 'help-inline',
		 'errorElement'   => 'span',
		 'errorPlacement' => 'function(error,element){error.appendTo(element.parent("div"));}',
		 'rules'          => array(
			 'DreamLoginForm[email_addr_text]' => array(
				 'required' => true,
				 'email'    => true,
			 ),
		 ),
	)
);

if ( !isset( $modelName ) )
{
	$modelName = 'DreamLoginForm';
}
else
{
	$modelName = str_replace( '\\', '_', $modelName );
}

$_errors = null;

if ( !isset( $loginPost ) )
{
	$loginPost = false;
}

if ( !isset( $success ) )
{
	$success = false;
}

if ( isset( $model ) )
{
	$_errors = $model->getErrors();

	if ( !empty( $_errors ) )
	{
		$_message =
			'<div class="alert alert-error alert-fixed fade in" data-alert="alert"><strong>No sir, we don\'t like it.</strong>';

		foreach ( $_errors as $_error )
		{
			foreach ( $_error as $_value )
			{
				$_message .= '<p>' . $_value . '</p>';
			}
		}

		$_message .= '</div>';
	}
}
?>
<div id="loginbox">
	<form id="loginform" class="form-vertical" method="POST">
		<input type="hidden" name="recover" value="0">
		<div class="control-group normal_text logo-container"><h3><img src="/img/logo-cerberus-256x256.png" alt="" /></h3></div>
		<?php echo $_message; ?>
		<div class="control-group">
			<div class="controls">
				<div class="main_input_box">
					<span class="add-on bg_lg"><i class="icon-user"></i></span>

					<input class="email required" autofocus type="text" id="<?php echo $modelName; ?>_email_addr_text"
						   name="<?php echo $modelName; ?>[email_addr_text]" placeholder="Email Address" />
				</div>
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<div class="main_input_box">
					<span class="add-on bg_ly"><i class="icon-lock"></i></span>

					<input class="password required" id="<?php echo $modelName; ?>_password_text" placeholder="Password"
						   name="<?php echo $modelName; ?>[password_text]" type="password" />
				</div>
			</div>
		</div>
		<div class="form-actions">
			<span class="pull-left"><a href="#" class="flip-link btn btn-info" id="to-recover">Lost password?</a></span> <span class="pull-right"><button
					type="submit" class="btn btn-success"> Login
				</button></span>
		</div>
	</form>
	<form id="recoverform" class="form-vertical" action="/app/recover" method="POST">
		<input type="hidden" name="recover" value="1">
		<p class="normal_text">Enter your email address below and we will send you instructions how to recover a password.</p>

		<div class="controls">
			<div class="main_input_box">
				<span class="add-on bg_lo"><i class="icon-envelope"></i></span>

				<input class="email required" autofocus type="text" id="<?php echo $modelName; ?>_email_addr_text"
					   name="<?php echo $modelName; ?>[email_addr_text]" placeholder="Email Address" />
			</div>
		</div>

		<div class="form-actions">
			<span class="pull-left"><a href="#" class="flip-link btn btn-success" id="to-login">&laquo; Back to login</a></span> <span
				class="pull-right"><button class="btn btn-info" type="submit">Recover</button></span>
		</div>
	</form>
</div>
<script type="text/javascript">
$(document).ready(function() {
	$('#to-recover').on('click', function() {
		$('body').animate({"margin-top": "10%"});
		$("#loginform").slideUp();
		$("#recoverform").fadeIn();
	});

	$('#to-login').on('click', function() {
		$("#recoverform").fadeOut();
		$('body').animate({"margin-top": "20px"});
		$("#loginform").slideDown();
	});
});
</script>