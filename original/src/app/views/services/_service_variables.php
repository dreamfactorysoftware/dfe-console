<?php
/**
 * _services_variables.php
 * Partial view for all variables for a service
 *
 * @var string            $header
 * @var Service           $service
 * @var ServiceVariable[] $variables
 * @var WebController     $this
 */

$_button = null;

\PS::_rcf( '/css/df.datatables.css' );
\PS::_rsf( '/vendor/datatables/js/jquery.dataTables.js', CClientScript::POS_END );
\PS::_rsf( '/js/df.datatables.js', CClientScript::POS_END );
\PS::_rsf( '/js/jquery.jeditable.min.js', \CClientScript::POS_END );
\PS::_rsf( '/js/jquery.dataTables.editable.js', \CClientScript::POS_END );

if ( $update )
{
	$_button = <<<HTML
<div style="width:100%; margin-bottom: 10px;" class="pull-right"><a class="btn btn-primary btn-small btn-info" href="#variable-editor" data-toggle="modal">Add Variable</a></div>
HTML;
}
?>
<div class="tab-pane" id="service-config-tab-variables">
	<?php echo $_button; ?>
	<table id="variable-editor-table" class="table table-condensed table-striped table-bordered table-hover dataTable">
		<thead>
		<tr>
			<th class="center">&nbsp;ID&nbsp;</th>
			<th>Name</th>
			<th>Value</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ( $variables as $_id => $_variable )
		{
			$_payload = new \stdClass();
			$_payload->serviceId = $service->id;
			$_payload->variable = $_variable;

			$_idHash = \Kisma\Core\Utility\Hasher::encryptString( json_encode( $_payload ), ServicesController::SaltyGoodness, true );

			echo <<<HTML
<tr id="{$_idHash}">
	<td class="service-variable">{$_id}</td>
	<td>{$_variable['name_text']}</td>
	<td>{$_variable['value_text']}<i title="Click the trash can to delete this item" class="icon-trash pull-right variable-editor-delete-variable" style="display:none;margin-top:3px;"></i></td>
</tr>
HTML;
		}
		?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
	var _baseUrl = '/services/updateVariable/serviceId/<?php echo $service->id;?>';
	var _dataTable = null;

	$(function () {

		//	Initialize the datatable
		_dataTable = $('.dataTable').dataTable({
			sPaginationType: 'bootstrap',
			aLengthMenu:
				[
					[
						10,
						25,
						50,
						-1
					],
					[
						10,
						25,
						50,
						"All"
					]
				],

			// set the initial value
			iDisplayLength: 25,
			aaSorting:
				[
					[
						1,
						'asc'
					],
				],
			oLanguage: {
				sLengthMenu: '_MENU_ per page',
				sSearch: 'Search:',
				oAria: {
					sSortAscending: ': Click to sort ascending',
					sSortDescending: ': Click to sort descending'
				}
			},
			oClasses: {
				sFilter: 'input-xlarge'
			},
			aoColumns:
				[
					{
						sWidth: '50px'
					},
					{
						sWidth: '200px'
					},
					null
				]
		}).makeEditable({
				sUpdateURL: _baseUrl,
				aoColumns:
					[
						null,
						{
							indicator: '<span><img class="inline-loading" style="margin-right: 5px;" src="/img/ui-anim_basic_16x16.gif">Saving...</span>',
							onblur: 'cancel',
							type: 'text',
							submit: '<button class="btn btn-primary btn-mini" style="margin-top: 5px; margin-right: 5px;" type="submit">Save</button>',
							cancel: '<button class="btn btn-secondary btn-mini" style="margin-top: 5px; margin-right: 5px;" type="cancel">Cancel</button>',
							cssclass: 'var-edit',
							tooltip: 'Double-click to edit...',
							width: '98%'
						},
						{
							indicator: '<span><img class="inline-loading" style="margin-right: 5px;" src="/img/ui-anim_basic_16x16.gif">Saving...</span>',
							onblur: 'cancel',
							type: 'text',
							submit: '<button class="btn btn-primary btn-mini" style="margin-top: 5px; margin-right: 5px;" type="submit">Save</button>',
							cancel: '<button class="btn btn-secondary btn-mini" style="margin-top: 5px; margin-right: 5px;" type="cancel">Cancel</button>',
							cssclass: 'var-edit',
							tooltip: 'Double-click to edit...',
							width: '95%'
						}
					]
			});

		$('td.service-variable-value').change(function () {
			var _parts = $(this).val().split(';');
			if (_parts && _parts.length) {
				var _val = '';
				for (_i = 0; _i < _parts.length; _i++) {
					_val += '<span class="badge badge-info">' + _parts[_i] + '</span>';
				}
				$(this).val(_val);
			}
		});

		$('#variable-editor').find('form').validate({
			highlight: function (element, errorClass) {
				_highlightError(element, errorClass);
			},
			unhighlight: function (element, errorClass) {
				_unhighlightError(element, errorClass);
			}
		});

		/**
		 * Show the trash can icon on hover
		 */
		$('table#variable-editor-table.table tbody tr').on('mouseenter mouseleave', function (e) {
			if ('mouseenter' == e.type) {
				$('td i.icon-trash', $(this)).show();
			} else {
				$('td i.icon-trash', $(this)).hide();
			}
		});

		$('table#variable-editor-table.table tbody tr td i.icon-trash').on('click', function (e) {
			e.preventDefault();
			if (confirm('Delete this variable permanently?')) {
				var $_trash = $(this);
				$.ajax('/services/updateVariable/serviceId/<?php echo $service->id; ?>/', {
					type: 'POST',
					async: false,
					data: {"id": $(this).closest('tr').attr('id'), "delete": 1},
					complete: function () {
						$_trash.closest('tr').remove();
						alert('Deleted!');
					}
				});
			}
		});

	});
</script>
<div id="variable-editor" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3>New Variable</h3>
	</div>
	<form action="/services/addVariable/" method="POST">
		<input type="hidden" name="service_id" value="<?php echo $service->id; ?>" />

		<div class="modal-body">
			<div class="control-group">
				<label class="control-label" for="name_text">Name</label>

				<div class="controls">
					<input type="text" value="" class="required input-xxlarge" name="name_text" id="name_text" />
				</div>
			</div>
			<p></p>
			<label for="value_text">Value</label>
			<textarea class="input-xxlarge" name="value_text" id="value_text"></textarea>
		</div>
		<div class="modal-footer">
			<a data-dismiss="modal" href="#" class="btn">Close</a>
			<button type="submit" class="btn btn-primary">Save changes</button>
		</div>
	</form>
</div>
