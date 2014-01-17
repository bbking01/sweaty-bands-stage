if (!QuickBuyHelper) {
	var QuickBuyHelper = {};
}

QuickBuyHelper.toogleFieldEditMode = function(id, container) {
	$(container).disabled = $(id).checked;
	$(container).up().select('input').each(function(elm) {
		elm.disabled = $(id).checked;
	});
};

function toogleItorisElement(id, isDisabled) {
	var elm = $(id);
	var row = elm.up('tr');
	var checkbox = row.select('input[type=checkbox]')[0];
	if (checkbox) {
		checkbox.disabled = isDisabled;
		if (checkbox.disabled) {
			elm.disabled = true;
		} else {
			elm.disabled = checkbox.checked;
		}
	} else {
		elm.disabled = isDisabled;
	}
}