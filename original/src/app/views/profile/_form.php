<?php
/**
 * _form.php
 *
 * @var ProfileController  $this
 * @var array              $_formOptions
 * @var ServiceConfig      $model
 */
\PS::setFormFieldContainerClass( 'control-group' );
\PS::setShowRequiredLabel( false );
\PS::setLabelSuffix( null );
\PS::_rsf( '/js/jquery.multientry.js', CClientScript::POS_END );

$_defaultVariables = $model->config_text;

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
	array( 'html', $_tabs ),
);

$_fieldList[] = array(
	'html',
	\Kisma\Core\Utility\HtmlMarkup::tag( 'input', array( 'class' => 'uneditable-input' ), $model->service->service_name_text ),
);

////	Variables
//$_fieldList[] =
//	array(
//		'html',
//		'<div style="padding-top: 18px;" data-model="Service" data-attribute="default_variables_text" id="required-variables"></div>'
//	);

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
		window.location.href = '/profile/';
	});

	$('#service-config-tabs').find('li.disabled').on('click', function(e) {
		return false;
	});

	$('#service-config-tabs').find('a:first').tab('show');

<!--	$('div#required-variables').multientry({-->
<!--		formId:      'ps-edit-form',-->
<!--		label:       'Default Variables',-->
<!--		placeholder: 'Enter a variable name',-->
<!--		hidden:      false,-->
<!--		items:       [--><?php //echo implode( ',', $_defaultVariables );?><!--]-->
<!--	});-->

});
</script>