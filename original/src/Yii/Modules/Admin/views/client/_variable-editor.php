<?php
/**
 * _variable-editor.php
 *
 * @var ServiceVariable $service
 */
\PS::setFormFieldContainerClass( 'control-group' );
\PS::setShowRequiredLabel( false );
\PS::setLabelSuffix( '' );
$_model = new ServiceVariable();
$_model->service_id = $service->id;

$_formOptions['formModel'] = $_model;
$_formOptions['validateOptions'] = array(
	'highlight'   => 'function(element,errorClass){_highlightError(element,errorClass);}',
	'unhighlight' => 'function(element,errorClass){_unhighlightError(element,errorClass);}',
);

$_fieldList = array();

$_fieldList[] = array(
	PS::TEXT,
	'name_text',
	array(
		'size'        => 60,
		'maxlength'   => 255,
		'class'       => 'required input-xxlarge',
	)
);

$_fieldList[] = array(
	PS::TEXTAREA,
	'value_text',
	array(
		'class'       => 'required input-xxlarge',
	)
);

$_fieldList[] = array(
	PS::HIDDEN,
	'service_id',
	array(
		'value' => $service->id,
	)
);

$_formOptions['fields'] = $_fieldList;

CPSForm::create( $_formOptions );
?>
<script type="text/javascript">
$(function() {
});
</script>
