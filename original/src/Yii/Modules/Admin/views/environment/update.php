<?php
/**
 * @var EnvironmentController $this
 * @var Environment           $model
 */

use DreamFactory\Yii\Utility\BootstrapForm;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Interfaces\FormTypes;
use Kisma\Core\Utility\Bootstrap;

$_modelName = 'Environment';
$_form = new BootstrapForm( FormTypes::Horizontal );

$_formOptions = $_form->pageHeader(
	array(
		'header'           => 'Deployment Environment',
		'sub_header'       => $update ? $model->environment_name_text : 'New Environment',
		'breadcrumbs'      => array(
			'<i class="icon-home icon-white" style="margin-right: 4px;"></i>Home' => '/',
			'Environment Manager'                                                 => '/admin/environment/',
			$update ? $model->environment_name_text : 'New Environment'           => false,
		),
		'id'               => 'env-bootstrap',
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

$_fields = array(
	'General' => array(
		'environment_name_text' => array( 'class' => 'input-xlarge required', 'label' => 'Name' ),
	),
);
?>
<form id="env-bootstrap" method="POST" class="form-horizontal" action>
	<?php $_form->renderFields( $_fields, $_modelName );?>

	<div class="form-actions">
		<button type="submit" class="btn btn-primary"><?php echo ( $update ? 'Save' : 'Create' ); ?></button>
		<button type="reset" class="btn btn-secondary">Cancel</button>
	</div>
</form>
