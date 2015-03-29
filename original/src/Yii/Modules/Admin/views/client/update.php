<?php
/**
 * @var ClientController     $this
 * @var \User                $model
 */

use DreamFactory\Yii\Utility\BootstrapForm;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Interfaces\FormTypes;
use Kisma\Core\Utility\Bootstrap;

$_modelName = 'User';
$_form = new BootstrapForm( FormTypes::Horizontal );

$_formOptions = $_form->pageHeader(
	array(
		'header'           => 'Client Information',
		'sub_header'       => $update ? $model->email_addr_text : 'New User',
		'breadcrumbs'      => array(
			'<i class="icon-home icon-white" style="margin-right: 4px;"></i>Home' => '/',
			'Client Manager'                                                      => '/admin/client/',
			$update ? $model->email_addr_text : 'New User'                        => false,
		),
		'id'               => 'client-bootstrap',
		'method'           => 'POST',
		//	Set up validation...
		'validate'         => true,
		'validate_options' => array(
			//	Validation Rules
			'rules' => array(),
		),
		'model'            => $model,
		'model_name'       => $_modelName,
	)
);

$_form->setFormData( $model->getAttributes() );

$_accountFields = array(
	'Account Information' => array(
		'email_addr_text' => array( 'class' => 'input-xlarge required email', 'required' => 'required', 'label' => 'Email Address' ),
	),
);

$_personalFields = array(
	'Personal Information' => array(
		'first_name_text'   => array( 'class' => 'input-large required' ),
		'last_name_text'    => array( 'class' => 'input-large required' ),
		'display_name_text' => array( 'class' => 'input-large required' ),
	),
);

$_companyFields = array(
	'Company Information' => array(
		'company_name_text'   => array( 'class' => 'input-xlarge' ),
		'title_text'          => array( 'class' => 'input-large' ),
		'city_text'           => array( 'class' => 'input-large' ),
		'state_province_text' => array( 'class' => 'input-large' ),
		'country_text'        => array( 'class' => 'input-large', 'type' => 'select', 'contents' => require( '_countries.php' ) ),
		'postal_code_text'    => array( 'class' => 'postalCode' ),
		'phone_text'          => array( 'class' => 'input-medium phoneUS' ),
		'fax_text'            => array( 'class' => 'input-medium phoneUS' ),
	),
);
?>
<form id="client-bootstrap" method="POST" class="form-horizontal" action>
	<div class="row-fluid">
		<div class="span6">
			<?php $_form->renderFields( $_accountFields, $_modelName );?>
		</div>
		<div class="span6">
			<?php $_form->renderFields( $_personalFields, $_modelName );?>
		</div>
	</div>

	<div class="row-fluid">
		<div class="span12">
			<?php $_form->renderFields( $_companyFields, $_modelName );?>
		</div>
	</div>

	<div class="control-group">
		<div class="controls">
			<label class="checkbox">
				<input checked="checked" value="1" id="<?php echo $_modelName; ?>_opt_in_ind" name="<?php echo $_modelName; ?>[opt_in_ind]"
					   type="checkbox" />
				Opted-in for emails
			</label>
		</div>
	</div>

	<div class="form-actions">
		<button type="submit" class="btn btn-primary"><?php echo ( $update ? 'Save' : 'Create' ); ?></button>
		<button type="reset" class="btn btn-secondary">Cancel</button>
	</div>
</form>
