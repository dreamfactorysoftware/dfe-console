<?php
/**
 * @var ServicesController $this
 * @var Service            $model
 */
$_html = null;

$this->setStandardFormOptions(
	$model,
	array(
		'uiStyle'       => \PS::UI_BOOTSTRAP,
		'title'         => \PS::_gan() . ' :: Login',
		'header'        => 'Service Manager',
		'breadcrumbs'   => array( 'Home' => '/', 'Service Manager' => false ),
	)
);

PS::_rcf( '/css/df.datatables.css' );
PS::_rsf( '/vendor/datatables/js/jquery.dataTables.js', CClientScript::POS_END );
PS::_rsf( '/js/df.datatables.js', CClientScript::POS_END );

/** @var $_services Service[] */
if ( null !== ( $_services = Service::model()->findAll() ) )
{
	foreach ( $_services as $_service )
	{
		$_html .= '<tr>';
		$_html .= '<td class="ctr">' . $_service->id . '</td>';
		$_html .= '<td>' . $_service->service_tag_text . '</td>';
		$_html .= '<td>' . $_service->service_name_text . '</td>';
		$_html .= '<td class="ctr">' . ( 1 == $_service->enable_ind ? 'Yes' : 'No' ) . '</td>';
		$_html .= '<td class="ctr">' . ( 1 == $_service->public_ind ? 'Yes' : 'No' ) . '</td>';
		$_html .= '</tr>';
		unset( $_service );
	}
}

?>
<table class="table table-striped table-bordered table-hover" id="services-table">
	<thead>
		<tr>
			<th class="ctr">ID</th>
			<th>Tag</th>
			<th>Name</th>
			<th class="ctr">Enabled</th>
			<th class="ctr">Public</th>
		</tr>
	</thead>
	<tbody>
		<?php echo $_html; ?>
	</tbody>
</table>
<script type="text/javascript">
$(function() {
	$('#services-table').dataTable({
		"sDom":            "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
		"sPaginationType": "bootstrap",
		"oLanguage":       {
			"sSearch":     "Filter:",
			"sLengthMenu": "_MENU_ records per page"
		}
	});

	/* Add events */
	$("#services-table").find("tbody tr").on('click', function() {
		var _row = $('td', this);
		var _id = $(_row[0]).text();
		window.location.href = '/services/update/id/' + _id;
		return false;
	});

	_addBreadcrumbButton('Add New Service', '/services/create/');
});
</script>