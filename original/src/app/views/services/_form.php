<?php
/**
 * _form.php
 *
 * @var ServicesController $this
 * @var array              $_formOptions
 * @var Service            $model
 */
\PS::setFormFieldContainerClass( 'control-group' );
\PS::setShowRequiredLabel( false );
\PS::setLabelSuffix( '' );
\PS::_rsf( '/js/jquery.multientry.js', CClientScript::POS_END );

$_defaultVariables =
	is_array( $model->default_variables_text ) ? $model->default_variables_text : explode( ';', trim( $model->default_variables_text, ' ;' ) );

if ( !is_array( $_defaultVariables ) || empty( $_defaultVariables ) )
{
	$_defaultVariables = array();
}
else
{
	foreach ( $_defaultVariables as &$_variable )
	{
		$_variable = '\'' . $_variable . '\'';
	}
}

$_formOptions['formClass'] = 'form-horizontal';
$_formOptions['validateOptions'] = array(
	'highlight'   => 'function(element,errorClass){_highlightError(element,errorClass);}',
	'unhighlight' => 'function(element,errorClass){_unhighlightError(element,errorClass);}',
);

$_classList = $this->findControllers();

$_liClass = !$update ? ' class="disabled" ' : null;

$_tabs = <<<HTML
<ul class="nav nav-tabs" id="service-config-tabs">
	<li><a href="#service-config-tab-general" data-toggle="tab">General</a></li>
  	<li {$_liClass}><a href="#service-config-tab-statistics" data-toggle="tab">Statistics</a></li>
</ul>
<div class="tab-content">
  <div class="tab-pane" id="service-config-tab-statistics"><h2>Coming Soon!</h2></div>
  <div class="tab-pane" id="service-config-tab-general">
HTML;

$_fieldList = array(
	array( 'html', $_tabs ), //'<legend>Service Configuration</legend>' ),
);

$_fieldList[] = array(
	PS::TEXT,
	'service_name_text',
	array(
		'size'      => 60,
		'maxlength' => 255,
		'class'     => 'required input-xxlarge',
	)
);

$_fieldList[] = array(
	PS::TEXT,
	'service_tag_text',
	array(
		'size'      => 30,
		'maxlength' => 60,
		'class'     => 'required input-large',
	)
);

$_fieldList[] = array(
	PS::DD_GENERIC,
	'controller_class_text',
	array(
		'data'   => $_classList,
		'class'  => 'input-medium',
		'prompt' => 'Automatic',
	)
);

$_fieldList[] = array( PS::TEXTAREA, 'description_text', array( 'rows' => 10, 'cols' => 80, 'class' => 'input-xxlarge' ) );

//	Required Variables
$_fieldList[] =
	array(
		'html',
		'<div style="padding-top: 18px;" data-model="Service" data-attribute="default_variables_text" id="required-variables"></div>'
	);

$_fieldList[] = array( PS::CHECK, 'public_ind', array( 'value' => 1, ) );
$_fieldList[] = array( PS::CHECK, 'enable_ind', array( 'value' => 1, ) );

//$_fieldList[] = array( PS::CHECK, 'alive_ind', array( 'value' => 1, ) );

$_value = ( $update ? 'Save' : 'Create' );

$_html = '</div>'; //	Ends the tab-content

$_html .= <<<HTML
	<div class="form-actions">
		<input type="submit" class="btn btn-primary" value="{$_value}">
		&nbsp;
		<button type="reset" class="btn">Cancel</button>
	</div>
HTML;

$_fieldList[] = array( 'html', $_html );

$_formOptions['fields'] = $_fieldList;

CPSForm::create( $_formOptions );
?>
<script type="text/javascript">
//var _classList = <?php echo json_encode( $_classList ); ?>;
$(function() {
	$('button[type="reset"]').click(function() {
		window.location.href = '/services/';
	});

	$('#service-config-tabs').find('li.disabled').on('click', function(e) {
		return false;
	});

	$('#service-config-tabs').find('a:first').tab('show');

	$('div#required-variables').multientry({
		formId:      'ps-edit-form',
		label:       'Default Variables',
		placeholder: 'Enter a variable name',
		hidden:      false,
		items:       [<?php echo implode( ',', $_defaultVariables );?>]
	});

});
</script>