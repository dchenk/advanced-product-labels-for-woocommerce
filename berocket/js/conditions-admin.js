var conditionExample = jQuery("#br_condition_example");
var $html = '<div class="br_cond_one">'+conditionExample.html()+'</div>';
jQuery(document).on('change', '.br_cond_type', function(event) {
	var $parent = jQuery(this).parents('.br_cond_select');
	$parent.find('.br_cond').remove();
	var id = $parent.parents('.br_html_condition');
	var current_id = $parent.data('current');
	id = id.data('id');
	var html_need = document.querySelector("#condition-types-example .br_cond_"+jQuery(this).val()).outerHTML;
	html_need = html_need.replace(/%id%/g, id);
	html_need = html_need.replace(/%current_id%/g, current_id);
	html_need = html_need.replace(/%name%/g, condition_name);
	$parent.find('.br_current_cond').html(html_need);
});
jQuery(document).on('click', '.berocket_add_condition', function() {
	var id = jQuery(this).parents('.br_html_condition');
	var current_id = id.data('current');
	current_id = current_id + 1;
	id.data('current', current_id);
	id = id.data('id');
	var $html = conditionExample.find(".br_cond_select").html();
	$html = '<div class="br_cond_select" data-current="'+current_id+'">'+$html+'</div>';
	$html = $html.replace('%id%', id);
	jQuery(this).before($html);
	jQuery(this).prev().find('.br_cond_type').trigger('change');
});
jQuery(document).on('click', '.berocket_remove_condition', function() {
	jQuery(this).parents('.br_cond_select').remove();
});
jQuery(document).on('click', '.br_add_group', function() {
	last_id++;
	var html = $html.replace('%id%', last_id);
	var html = '<div class="br_html_condition" data-id="'+last_id+'" data-current="1">'+html+'</div>';
	jQuery(this).before(html);
	jQuery(this).prev().find('.br_cond_type').trigger('change');
});
jQuery(document).on('click', '.br_remove_group', function() {
	jQuery(this).parents('.br_html_condition').remove();
});
jQuery(document).on('change', '.br_cond_attr_select', function() {
	var $attr_block = jQuery(this).parents('.br_cond_attribute, .br_cond_woo_attribute');
	$attr_block.find('.br_attr_values').hide();
	$attr_block.find('.br_attr_value_'+jQuery(this).val()).show();
});
jQuery(document).on('change', '.price_from', function() {
	var val_price_from = jQuery(this).val();
	var val_price_to = jQuery(this).parents('.br_cond').first().find('.price_to').val();
	price_from = parseFloat(val_price_from);
	price_to = parseFloat(val_price_to);
	price_to_int = parseInt(val_price_to);
	if( val_price_from == '' ) {
		jQuery(this).val(0);
		price_from = 0;
	}
	if( price_from > price_to ) {
		jQuery(this).val(price_to_int);
	}
});
jQuery(document).on('change', '.price_to', function() {
	var val_price_from = jQuery(this).parents('.br_cond').first().find('.price_from').val();
	var val_price_to = jQuery(this).val();
	price_from = parseFloat(val_price_from);
	price_from_int = parseInt(val_price_from);
	price_to = parseFloat(val_price_to);
	if (val_price_to === '') {
		jQuery(this).val(0);
		price_to = 0;
	}
	if (price_from > price_to) {
		jQuery(this).val(price_from_int);
	}
});