
jQuery(document).ready(function(){

	jQuery('.data-table').dataTable({
		"bJQueryUI": true,
		"sPaginationType": "full_numbers",
		"sDom": '<""l>t<"F"fp>'
	});

	jQuery('input[type=checkbox],input[type=radio],input[type=file]').uniform();

	jQuery('select').select2();

	jQuery("span.icon input:checkbox, th input:checkbox").click(function() {
		var checkedStatus = this.checked;
		var checkbox = jQuery(this).parents('.widget-box').find('tr td:first-child input:checkbox');
		checkbox.each(function() {
			this.checked = checkedStatus;
			if (checkedStatus == this.checked) {
				jQuery(this).closest('.checker > span').removeClass('checked');
			}
			if (this.checked) {
				jQuery(this).closest('.checker > span').addClass('checked');
			}
		});
	});
});
