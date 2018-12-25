var lastCondID = window.largestCondID;

if (window.currentCondData) {
	console.log("currentCondData");
	console.log(window.currentCondData);
}

function makeCondName(condType, id, position) {
	return `${condType}[${id}][${position}][type]`;
}

// Add a condition group.
jQuery("#apl_add_cond_group").on("click", function() {
	lastCondID++;
	let html = window.condGroupTemplate.
		replace("SELECT_TEMPLATE", window.condSelectTemplate).
		replace(/%id%/g, lastCondID).
		replace("DATA_CURRENT", lastCondID);
	html = `<div class="br_html_condition" data-id="${lastCondID}" data-current="1">${html}</div>`;
	jQuery(this).before(html);
	jQuery(this).prev().find(".br_cond_type").trigger("change");
});

// Add a condition to a group.
jQuery(document).on("click", ".berocket_add_condition", function() {
	const parent = jQuery(this).parents(".br_html_condition");
	let current_id = Number(parent.data("current"));
	current_id++;
	parent.data("current", current_id);
	let html = window.condSelectTemplate.
		replace(/%id%/g, current_id).
		replace("DATA_CURRENT", current_id);
	jQuery(this).before(html);
	jQuery(this).prev().find(".br_cond_type").trigger("change");
});

// Specify the type of a condition.
jQuery(document).on("change", ".br_cond_type", function(_) {
	const parent = jQuery(this).parents(".br_cond_select");
	parent.find(".br_cond").remove();
	const id = parent.parents(".br_html_condition").data("id");
	const current_id = parent.data("current");
	const condType = jQuery(this).val();
	let tmpl = window.condTypeTemplates[condType];
	if (!tmpl) {
		alert("An error occurred.");
		return;
	}
	tmpl = tmpl.replace(/%id%/g, id).
		replace(/%current_id%/g, current_id).
		replace(/%name%/g, condType);
	parent.find(".br_current_cond").html(tmpl);
});

jQuery(document).on("click", ".berocket_remove_condition", function() {
	jQuery(this).parents(".br_cond_select").remove();
});

jQuery(document).on("click", ".br_remove_group", function() {
	jQuery(this).parents(".br_html_condition").remove();
});

jQuery(document).on("change", ".br_cond_attr_select", function() {
	var $attr_block = jQuery(this).parents(".br_cond_attribute, .br_cond_woo_attribute");
	$attr_block.find(".br_attr_values").hide();
	$attr_block.find(".br_attr_value_"+jQuery(this).val()).show();
});

jQuery(document).on("change", ".price_from", function() {
	var val_price_from = jQuery(this).val(),
		val_price_to = jQuery(this).parents(".br_cond").first().find(".price_to").val(),
		price_from = parseFloat(val_price_from),
		price_to = parseFloat(val_price_to),
		price_to_int = parseInt(val_price_to);
	if (!val_price_from) {
		jQuery(this).val(0);
		price_from = 0;
	}
	if (price_from > price_to) {
		jQuery(this).val(price_to_int);
	}
});

jQuery(document).on("change", ".price_to", function() {
	var val_price_from = jQuery(this).parents(".br_cond").first().find(".price_from").val(),
		val_price_to = jQuery(this).val(),
		price_from = parseFloat(val_price_from),
		price_from_int = parseInt(val_price_from),
		price_to = parseFloat(val_price_to);
	if (!val_price_to) {
		jQuery(this).val(0);
		price_to = 0;
	}
	if (price_from > price_to) {
		jQuery(this).val(price_from_int);
	}
});