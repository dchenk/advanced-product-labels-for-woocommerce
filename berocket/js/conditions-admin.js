(function() {
	let lastCondID = window.largestCondGroupID;
	const condsList = document.getElementById("apl-conditions-list");

	function addGroup(id) {
		let html = window.condGroupTemplate.
			replace("SELECT_TEMPLATE", window.condSelectTemplate).
			replace(/%id%/g, id).
			replace("DATA_CURRENT", id);
		const elem = document.createElement("DIV");
		elem.classList.add("br_html_condition");
		elem.dataset.id = id;
		elem.dataset.current = "1";
		elem.innerHTML = html;
		condsList.appendChild(elem);
		setCondType(elem, data.type);
		elem.querySelector("select.br_cond_type").addEventListener("change", setCondType.bind(null, elem));
		elem.querySelector(".berocket_add_condition").addEventListener("click", addCondition.bind(null, elem));
		elem.querySelector(".br_remove_group").addEventListener("click", function() {
			elem.remove();
		});
	}

	// Add a condition group.
	jQuery("#apl-add-cond-group").on("click", function() {
		lastCondID++;
		addGroup(lastCondID);
	});

	function addCondition(groupElement) {
		const id = parent.parents(".br_html_condition").data("id");
		const position = parent.data("current");
		let tmpl = window.condTypeTemplates[jQuery(this).val()];
		if (!tmpl) {
			return;
		}
		tmpl = tmpl.replace(/%id%/g, id).
		replace(/%current_pos%/g, position);
		parent.find(".apl-cond-options").html(tmpl);
	}

	function setCondType(groupElement, newType) {
		let position = Number(parent.data("current"));
		position++;
		parent.data("current", position);
		let html = window.condSelectTemplate.
		replace(/%id%/g, position).
		replace("DATA_CURRENT", position);
		jQuery(this).before(html);
		jQuery(this).prev().find(".br_cond_type").trigger("change");
	}

	// Specify the type of a condition.
	// Register the listener on existing conditions on page load, and all other listeners are attached when created.
	jQuery("select.br_cond_type").on("change", function() {
		const parent = jQuery(this).parents(".apl-condition");
		parent.find(".br_cond").remove();
		addCondition(parent);
	});

	jQuery(document).on("click", ".berocket_remove_condition", function() {
		jQuery(this).parents(".apl-condition").remove();
	});


	jQuery(document).on("change", ".br_cond_attr_select", function() {
		const $attr_block = jQuery(this).parents(".br_cond_attribute, .br_cond_woo_attribute");
		$attr_block.find(".br_attr_values").hide();
		$attr_block.find(".br_attr_value_"+jQuery(this).val()).show();
	});

	jQuery(document).on("change", ".price_from", function() {
		let val_price_from = jQuery(this).val(),
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
		const val_price_from = jQuery(this).parents(".br_cond").first().find(".price_from").val(),
			val_price_to = jQuery(this).val(),
			price_from = parseFloat(val_price_from),
			price_from_int = parseInt(val_price_from);
		let priceTo = parseFloat(val_price_to);
		if (!val_price_to) {
			jQuery(this).val(0);
			priceTo = 0;
		}
		if (price_from > priceTo) {
			jQuery(this).val(price_from_int);
		}
	});
})();
