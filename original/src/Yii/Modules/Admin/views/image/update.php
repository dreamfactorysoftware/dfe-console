<?php
/**
 * @var ImageController $this
 * @var \VendorImage    $model
 */

use DreamFactory\Yii\Utility\BootstrapForm;
use DreamFactory\Yii\Utility\Pii;
use Kisma\Core\Interfaces\FormTypes;
use Kisma\Core\Utility\Bootstrap;

$_modelName = 'VendorImage';
$_form = new BootstrapForm( FormTypes::Horizontal );
$_subHeader = $update ? $model->image_id_text : 'New User';

$_formOptions = $_form->pageHeader(
	array(
		'header'           => 'Image Information',
		'sub_header'       => $_subHeader,
		'breadcrumbs'      => array(
			'<i class="icon-home icon-white" style="margin-right: 4px;"></i>Home' => '/',
			'Image Manager'                                                       => '/admin/image/',
			$_subHeader                                                           => false,
		),
		'id'               => 'image-bootstrap',
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
	'Image Information' => array(
		'Image ID' => array( 'class' => 'input-xlarge required', 'label' => 'Image ID' ),
		'Image Name' => array( 'class' => 'input-xlarge required', 'label' => 'Image Name' ),
	),
);
?>
<form id="image-bootstrap" method="POST" class="form-horizontal" action>
	<?php $_form->renderFields( $_fields, $_modelName );?>

	<div class="form-actions">
		<button type="submit" class="btn btn-primary"><?php echo ( $update ? 'Save' : 'Create' ); ?></button>
		<button type="reset" class="btn btn-secondary">Cancel</button>
	</div>
</form>
