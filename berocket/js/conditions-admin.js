(function() {
	let lastCondID = window.largestCondGroupID;
	const condsList = document.getElementById("apl-conditions-list");

	function addGroup(id) {
		const elem = document.createElement("DIV");
		elem.classList.add("br_html_condition");
		elem.dataset.id = id;
		elem.dataset.current = "1";
		elem.insertAdjacentHTML("beforeend", window.condButtons);
		addCondition(elem);
		condsList.appendChild(elem);
		elem.querySelector(".berocket_add_condition").addEventListener("click", addCondition.bind(null, elem));
		elem.querySelector(".br_remove_group").addEventListener("click", elem.remove);
	}

	// Add a condition group.
	jQuery("#apl-add-cond-group").on("click", function() {
		lastCondID++;
		addGroup(lastCondID);
	});

	/**
	 * @param groupElement HTMLElement
	 */
	function addCondition(groupElement) {
		const id = groupElement.dataset.id;
		const currentPos = groupElement.dataset.current;
		const newCurrent = (Number(currentPos) + 1).toString();
		groupElement.dataset.current = newCurrent;

		const tmpl = window.condTypeTemplates["product"].replace(/%id%/g, id).replace(/%current_pos%/g, newCurrent);

		const addButton = groupElement.querySelector(".berocket_add_condition");
		addButton.insertAdjacentHTML("beforebegin", tmpl);
		const inserted = addButton.previousSibling;
		inserted.dataset.current = newCurrent;
		const selectElem = inserted.querySelector("select.br_cond_type");
		selectElem.addEventListener("change", setCondType.bind(selectElem, inserted));
	}

	/**
	 * @this HTMLSelectElement The <select> element that was changed
	 * @param condWrapper HTMLElement The condition wrapper
	 */
	function setCondType(condWrapper) {
		const group = jQuery(condWrapper).parents(".br_html_condition")[0];
		const parentID = group.dataset.id;
		let position = Number(group.dataset.current);
		const newCurrent = (Number(position) + 1).toString();
		group.dataset.current = newCurrent;

		let tmpl = window.condTypeTemplates[this.value];
		if (!tmpl) {
			condWrapper.remove();
			return;
		}

		tmpl = tmpl.replace(/%id%/g, parentID).replace(/%current_pos%/g, position);
		condWrapper.insertAdjacentHTML("afterend", tmpl);
		const inserted = condWrapper.nextSibling;
		inserted.dataset.current = newCurrent;
		const selectElem = inserted.querySelector("select.br_cond_type");
		selectElem.addEventListener("change", setCondType.bind(selectElem, inserted));
		condWrapper.remove();
	}

	// Specify the type of a condition.
	// Register the listener on existing conditions on page load, and all other listeners are attached when created.
	jQuery("select.br_cond_type").on("change", function() {
		setCondType.call(this, jQuery(this).parents(".apl-condition")[0]);
	});

	jQuery(".berocket_add_condition").on("click", function() {
		addCondition(jQuery(this).parents(".br_html_condition")[0]);
	});

	jQuery(document).on("click", ".berocket_remove_condition", function() {
		jQuery(this).parents(".apl-condition").remove();
	});

	jQuery(".br_remove_group").on("click", function() {
		jQuery(this).parents(".br_html_condition").remove();
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
