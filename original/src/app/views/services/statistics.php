<?php
$this->setStandardFormOptions(
	$model,
	array(
		'id' => 'service-grid',
		'header' => 'Service Statistics',
		'headerIcon' => \DreamFactory\Yii\Utility\Pii::url( 'public/img/icon-services.png' ),
		'renderSearch' => true,
		'subtitle' => 'Service Statistics',
		'breadcrumbs' => array( 'Service Statistics' ),
		'menu' => array(
			'Create a Service' => array( 'create' ),
		),
	)
);

$this->widget(
	'zii.widgets.grid.CGridView',
	array(
		'id' => 'service-grid',
		'dataProvider' => $model->search(),
		'filter' => $model,
		'columns' => array(
			'service_name_text',
			'service_tag_text',
			'enable_ind',
			'public_ind',
			array(
				'class' => 'CButtonColumn',
				'template' => '{update} {delete}',
				'header' => 'Actions',
			),
		),
	)
);
?>
<script type="text/javascript">
$(function(){
	window.setTimeout("notify('default', { title: 'Statistics Unavailable', text: 'The service statistics are currently offline.' });",	2000 );
});
</script>