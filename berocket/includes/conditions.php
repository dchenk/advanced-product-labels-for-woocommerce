<?php

function br_supcondition_equal($name, $options, $extension = []) {
	$equal = 'equal';
	if (is_array($options) && isset($options['equal'])) {
		$equal = $options['equal'];
	}
	$equal_list = [
		'equal' => __('Equal', 'BeRocket_products_label_domain'),
		'not_equal' => __('Not equal', 'BeRocket_products_label_domain'),
	];
	if (! empty($extension['equal_less'])) {
		$equal_list['equal_less'] = __('Equal or less', 'BeRocket_products_label_domain');
	}
	if (! empty($extension['equal_more'])) {
		$equal_list['equal_more'] = __('Equal or more', 'BeRocket_products_label_domain');
	}
	$html = '<select name="' . $name . '[equal]">';
	foreach ($equal_list as $equal_slug => $equal_name) {
		$html .= '<option value="' . $equal_slug . '"' . ($equal == $equal_slug ? ' selected' : '') . '>' . $equal_name . '</option>';
	}
	$html .= '</select>';
	return $html;
}

class BeRocket_conditions {

	public $conditions = [];
	public $option_name;
	public $hook_name;

	public function __construct($option_name, $hook_name, $conditions = []) {
		$conditions = apply_filters($hook_name . '_conditions_list', $conditions);
		$this->conditions = $conditions;
		$this->option_name = $option_name;
		$this->hook_name = $hook_name;
		$ready_conditions = static::get_conditions();
		add_filter($hook_name . '_types', [$this, 'types']);
		foreach ($conditions as $condition) {
			if (isset($ready_conditions[$condition])) {
				//CONDITIONS HTML
				add_filter($hook_name . '_type_' . $ready_conditions[$condition]['type'], [get_class($this), $condition], 10, 3);
				//CONDITIONS CHECK
				add_filter($hook_name . '_check_type_' . $ready_conditions[$condition]['type'], [get_class($this), $ready_conditions[$condition]['func']], 10, 3);
				if (! empty($ready_conditions[$condition]['save'])) {
					add_filter($hook_name . '_save_type_' . $ready_conditions[$condition]['type'], [get_class($this), $ready_conditions[$condition]['save']], 10, 3);
				}
			} else {
				do_action($hook_name . '_condition_not_exist', $condition);
			}
		}
	}
	public function types($types) {
		$ready_conditions = static::get_conditions();
		foreach ($this->conditions as $condition) {
			if (isset($ready_conditions[$condition])) {
				$types[$ready_conditions[$condition]['type']] = $ready_conditions[$condition]['name'];
			}
		}
		return $types;
	}
	public function build(&$value, $additional = []) {
		if (! is_array($additional)) {
			$additional = [];
		}
		$additional['hook_name'] = $this->hook_name;
		return static::builder($this->option_name, $value, $additional);
	}
	public static function builder($name, &$value, $additional = []) {
		if (! isset($value) || ! is_array($value)) {
			$value = [];
		}
		ob_start();
		include_once(plugin_dir_path(__DIR__) . "templates/conditions.php");
		$html = ob_get_clean();
		return $html;
	}
	public static function check($conditions_data, $hook_name, $additional = []) {
		if (! is_array($conditions_data) || count($conditions_data) == 0) {
			$condition_status = true;
		} else {
			$condition_status = false;
			foreach ($conditions_data as $conditions) {
				$condition_status = false;
				foreach ($conditions as $condition) {
					$condition_status = apply_filters($hook_name . '_check_type_' . $condition['type'], false, $condition, $additional);
					if (!$condition_status) {
						break;
					}
				}
				if ($condition_status) {
					break;
				}
			}
		}
		return $condition_status;
	}
	public static function save($conditions_data, $hook_name) {
		if (! is_array($conditions_data) || count($conditions_data) == 0) {
			$conditions_data = [];
		} else {
			foreach ($conditions_data as $conditions_id => $conditions) {
				foreach ($conditions as $condition_id => $condition) {
					$conditions_data[$conditions_id][$condition_id] = apply_filters($hook_name . '_save_type_' . $condition['type'], $condition);
				}
			}
		}
		return $conditions_data;
	}
	public static function get_conditions() {
		return [
			//PRODUCTS
			'condition_product' => ['save' => 'save_condition_product', 'func' => 'check_condition_product', 'type' => 'product', 'name' => __('Product', 'BeRocket_domain')],
			'condition_product_sale' => ['func' => 'check_condition_product_sale', 'type' => 'sale', 'name' => __('On Sale', 'BeRocket_domain')],
			'condition_product_bestsellers' => ['func' => 'check_condition_product_bestsellers', 'type' => 'bestsellers', 'name' => __('Bestsellers', 'BeRocket_domain')],
			'condition_product_price' => ['func' => 'check_condition_product_price', 'type' => 'price', 'name' => __('Price', 'BeRocket_domain')],
			'condition_product_stockstatus' => ['func' => 'check_condition_product_stockstatus', 'type' => 'stockstatus', 'name' => __('Stock status', 'BeRocket_domain')],
			'condition_product_totalsales' => ['func' => 'check_condition_product_totalsales', 'type' => 'totalsales', 'name' => __('Total sales', 'BeRocket_domain')],
			'condition_product_category' => ['func' => 'check_condition_product_category', 'type' => 'category', 'name' => __('Category', 'BeRocket_domain')],
			'condition_product_attribute' => ['func' => 'check_condition_product_attribute', 'type' => 'attribute', 'name' => __('Product attribute', 'BeRocket_domain')],
			'condition_product_age' => ['func' => 'check_condition_product_age', 'type' => 'age', 'name' => __('Product age', 'BeRocket_domain')],
			'condition_product_saleprice' => ['func' => 'check_condition_product_saleprice', 'type' => 'saleprice', 'name' => __('Sale price', 'BeRocket_domain')],
			'condition_product_regularprice' => ['func' => 'check_condition_product_regularprice', 'type' => 'regularprice', 'name' => __('Regular price', 'BeRocket_domain')],
			'condition_product_stockquantity' => ['func' => 'check_condition_product_stockquantity', 'type' => 'stockquantity', 'name' => __('Stock quantity', 'BeRocket_domain')],
			'condition_product_featured' => ['func' => 'check_condition_product_featured', 'type' => 'featured', 'name' => __('Featured', 'BeRocket_domain')],
			'condition_product_shippingclass' => ['func' => 'check_condition_product_shippingclass', 'type' => 'shippingclass', 'name' => __('Shipping Class', 'BeRocket_domain')],
			'condition_product_type' => ['func' => 'check_condition_product_type', 'type' => 'product_type', 'name' => __('Product Type', 'BeRocket_domain')],
			'condition_product_rating' => ['func' => 'check_condition_product_rating', 'type' => 'product_rating', 'name' => __('Product Rating', 'BeRocket_domain')],
			//PAGES
			'condition_page_id' => ['func' => 'check_condition_page_id', 'type' => 'page_id', 'name' => __('Page ID', 'BeRocket_domain')],
			'condition_page_woo_attribute' => ['func' => 'check_condition_page_woo_attribute', 'type' => 'woo_attribute', 'name' => __('Product Attribute', 'BeRocket_domain')],
			'condition_page_woo_search' => ['func' => 'check_condition_page_woo_search', 'type' => 'woo_search', 'name' => __('Product Search', 'BeRocket_domain')],
			'condition_page_woo_category' => ['func' => 'check_condition_page_woo_category', 'type' => 'woo_category', 'name' => __('Product Category', 'BeRocket_domain')],
		];
	}
	public static function get_condition($condition) {
		$conditions = static::get_conditions_product();
		return ($conditions[$condition] ?? '');
	}
	public static function supcondition($name, $options, $extension = []) {
		$equal = 'equal';
		if (is_array($options) && isset($options['equal'])) {
			$equal = $options['equal'];
		}
		$equal_list = [
			'equal' => __('Equal', 'BeRocket_domain'),
			'not_equal' => __('Not equal', 'BeRocket_domain'),
		];
		if (! empty($extension['equal_less'])) {
			$equal_list['equal_less'] = __('Equal or less', 'BeRocket_domain');
		}
		if (! empty($extension['equal_more'])) {
			$equal_list['equal_more'] = __('Equal or more', 'BeRocket_domain');
		}
		$html = '<select name="' . $name . '[equal]">';
		foreach ($equal_list as $equal_slug => $equal_name) {
			$html .= '<option value="' . $equal_slug . '"' . ($equal == $equal_slug ? ' selected' : '') . '>' . $equal_name . '</option>';
		}
		$html .= '</select>';
		return $html;
	}
	public static function supcondition_check($value1, $value2, $condition) {
		$equal = 'equal';
		if (is_array($condition) && isset($condition['equal'])) {
			$equal = $condition['equal'];
		}
		$check = true;
		switch ($equal) {
			case 'equal':
				$check = $value1 == $value2;
				break;
			case 'not_equal':
				$check = $value1 != $value2;
				break;
			case 'equal_less':
				$check = $value1 <= $value2;
				break;
			case 'equal_more':
				$check = $value1 >= $value2;
				break;
		}
		return $check;
	}

	//PRODUCT CONDITION

	//HTML FOR PRODUCT CONDITIONS IN ADMIN PANEL
	public static function condition_product($html, $name, $options) {
		$def_options = ['product' => []];
		$options = array_merge($def_options, $options);
		$html .= static::supcondition($name, $options) . '
		<div class="br_framework_settings">' . br_products_selector($name . '[product]', $options['product']) . '</div>';
		return $html;
	}

	public static function condition_product_sale($html, $name, $options) {
		$def_options = ['sale' => 'yes'];
		$options = array_merge($def_options, $options);
		$html .= '<label>' . __('Is on sale', 'BeRocket_domain') . '<select name="' . $name . '[sale]">
			<option value="yes"' . ($options['sale'] == 'yes' ? ' selected' : '') . '>' . __('Yes', 'BeRocket_domain') . '</option>
			<option value="no"' . ($options['sale'] == 'no' ? ' selected' : '') . '>' . __('No', 'BeRocket_domain') . '</option>
		</select></label>';
		return $html;
	}

	public static function condition_product_bestsellers($html, $name, $options) {
		$def_options = ['bestsellers' => '1'];
		$options = array_merge($def_options, $options);
		$html .= '<label>' . __('Count of product', 'BeRocket_domain') . '<input type="number" min="1" name="' . $name . '[bestsellers]" value="' . $options['bestsellers'] . '"></label>';
		return $html;
	}

	public static function condition_product_featured($html, $name, $options) {
		$html .= static::supcondition($name, $options);
		return $html;
	}

	public static function condition_product_shippingclass($html, $name, $options) {
		$def_options = ['term' => ''];
		$options = array_merge($def_options, $options);
		$terms = get_terms('product_shipping_class');
		$terms_i = [];
		if (! empty($terms)) {
			foreach ($terms as $term) {
				$terms_i[$term->term_id] = $term->name;
			}
		}
		$html = static::supcondition($name, $options);
		$html .= '<select name="' . $name . '[term]">';
		foreach ($terms_i as $term_id => $term_name) {
			$html .= '<option value="' . $term_id . '"' . ($options['term'] == $term_id ? ' selected' : '') . '>' . $term_name . '</option>';
		}
		$html .= '</select>';
		return $html;
	}

	public static function condition_product_type($html, $name, $options) {
		$def_options = ['product_type' => ''];
		$options = array_merge($def_options, $options);
		$html = static::supcondition($name, $options);
		$html .= '<select name="' . $name . '[product_type]">';
		$product_types = wc_get_product_types();
		foreach ($product_types as $term_id => $term_name) {
			$html .= '<option value="' . $term_id . '"' . ($options['product_type'] == $term_id ? ' selected' : '') . '>' . $term_name . '</option>';
		}
		$html .= '</select>';
		return $html;
	}
	public static function condition_product_rating($html, $name, $options) {
		$def_options = ['has_rating' => ''];
		$options = array_merge($def_options, $options);
		$html .= __('Has Rating:', 'BeRocket_domain');
		$html .= '<select name="' . $name . '[has_rating]">';
		$html .= '<option value=""' . ($options['has_rating'] == '' ? ' selected' : '') . '>' . __('Yes', 'BeRocket_domain') . '</option>';
		$html .= '<option value="no"' . ($options['has_rating'] == 'no' ? ' selected' : '') . '>' . __('No', 'BeRocket_domain') . '</option>';
		$html .= '</select>';
		return $html;
	}

	public static function condition_product_price($html, $name, $options) {
		$def_options = ['price' => ['from' => '1', 'to' => '1']];
		$options = array_merge($def_options, $options);
		if (! is_array($options['price'])) {
			$options['price'] = [];
		}
		$options['price'] = array_merge($def_options['price'], $options['price']);
		$html .= static::supcondition($name, $options);
		$html .= __('From:', 'BeRocket_domain') . '<input class="price_from" type="number" min="0" name="' . $name . '[price][from]" value="' . $options['price']['from'] . '">' .
				 __('To:', 'BeRocket_domain') . '<input class="price_to"   type="number" min="1" name="' . $name . '[price][to]"   value="' . $options['price']['to'] . '">';
		return $html;
	}

	public static function condition_product_stockstatus($html, $name, $options) {
		$def_options = ['stockstatus' => 'in_stock'];
		$options = array_merge($def_options, $options);
		$html .= '
		<select name="' . $name . '[stockstatus]">
			<option value="in_stock"' . ($options['stockstatus'] == 'in_stock' ? ' selected' : '') . '>' . __('In stock', 'BeRocket_domain') . '</option>
			<option value="out_of_stock"' . ($options['stockstatus'] == 'out_of_stock' ? ' selected' : '') . '>' . __('Out of stock', 'BeRocket_domain') . '</option>
			<option value="is_on_backorder"' . ($options['stockstatus'] == 'out_of_stock' ? ' selected' : '') . '>' . __('On Backorder', 'BeRocket_domain') . '</option>
		</select>';
		return $html;
	}

	public static function condition_product_totalsales($html, $name, $options) {
		$def_options = ['totalsales' => '1'];
		$options = array_merge($def_options, $options);
		$html .= static::supcondition($name, $options, ['equal_less' => true, 'equal_more' => true]);
		$html .= '<label>' . __('Count of product', 'BeRocket_domain') . '<input type="number" min="0" name="' . $name . '[totalsales]" value="' . $options['totalsales'] . '"></label>';
		return $html;
	}

	public static function condition_product_category($html, $name, $options) {
		$def_options = ['category' => []];
		$options = array_merge($def_options, $options);
		if (! is_array($options['category'])) {
			$options['category'] = [$options['category']];
		}
		$product_categories = get_terms([
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
		]);
		if (is_array($product_categories) && count($product_categories) > 0) {
			$def_options = ['category' => ''];
			$options = array_merge($def_options, $options);
			$html .= static::supcondition($name, $options);
			$html .= '<label><input type="checkbox" name="' . $name . '[subcats]" value="1"' . (empty($options['subcats']) ? '' : ' checked') . '>' . __('Include subcategories', 'BeRocket_domain') . '</label>';
			$html .= '<div style="max-height:70px;overflow:auto;border:1px solid #ccc;padding: 5px;">';
			foreach ($product_categories as $category) {
				$html .= '<div><label>
				<input type="checkbox" name="' . $name . '[category][]" value="' . $category->term_id . '"' . ((! empty($options['category']) && is_array($options['category']) && in_array($category->term_id, $options['category'], true)) ? ' checked' : '') . '>
				' . $category->name . '
				</label></div>';
			}
			$html .= '</div>';
		}
		return $html;
	}

	public static function condition_product_attribute($html, $name, $options) {
		$def_options = ['attribute' => ''];
		$options = array_merge($def_options, $options);
		$attributes = get_object_taxonomies('product', 'objects');
		$product_attributes = [];
		foreach ($attributes as $attribute) {
			$attribute_i = [];
			$attribute_i['name'] = $attribute->name;
			$attribute_i['label'] = $attribute->label;
			$attribute_i['value'] = [];
			$terms = get_terms([
				'taxonomy' => $attribute->name,
				'hide_empty' => false,
			]);
			foreach ($terms as $term) {
				$attribute_i['value'][$term->term_id] = $term->name;
			}
			$product_attributes[] = $attribute_i;
		}
		$html .= static::supcondition($name, $options);
		$html .= '<label>' . __('Select attribute', 'BeRocket_domain') . '</label>';
		$html .= '<select name="' . $name . '[attribute]" class="br_cond_attr_select">';
		$has_selected_attr = false;
		foreach ($product_attributes as $attribute) {
			$html .= '<option value="' . $attribute['name'] . '"' . (isset($options['attribute']) && $attribute['name'] == $options['attribute'] ? ' selected' : '') . '>' . $attribute['label'] . '</option>';
			if ($attribute['name'] == $options['attribute']) {
				$has_selected_attr = true;
			}
		}
		$html .= '</select>';
		$is_first_attr = ! $has_selected_attr;
		foreach ($product_attributes as $attribute) {
			$html .= '<select class="br_attr_values br_attr_value_' . $attribute['name'] . '" name="' . $name . '[values][' . $attribute['name'] . ']"' . ($is_first_attr || $attribute['name'] == $options['attribute'] ? '' : ' style="display:none;"') . '>';
			foreach ($attribute['value'] as $term_id => $term_name) {
				$html .= '<option value="' . $term_id . '"' . (! empty($options['values'][$attribute['name']]) && $options['values'][$attribute['name']] == $term_id ? ' selected' : '') . '>' . $term_name . '</option>';
			}
			$html .= '</select>';
			$is_first_attr = false;
		}
		return $html;
	}

	public static function condition_product_age($html, $name, $options) {
		$def_options = ['age' => '1'];
		$options = array_merge($def_options, $options);
		$html .= br_supcondition_equal($name, $options, ['equal_less' => true, 'equal_more' => true]);
		$html .= '<input type="number" min="0" name="' . $name . '[age]" value="' . $options['age'] . '">' . __('day(s)', 'BeRocket_domain');
		return $html;
	}

	public static function condition_product_saleprice($html, $name, $options) {
		$def_options = ['saleprice' => ['from' => '1', 'to' => '1']];
		$options = array_merge($def_options, $options);
		if (! is_array($options['saleprice'])) {
			$options['saleprice'] = [];
		}
		$options['price'] = array_merge($def_options['saleprice'], $options['saleprice']);
		$html .= br_supcondition_equal($name, $options);
		$html .= __('From:', 'BeRocket_domain') . '<input class="price_from" type="number" min="0" name="' . $name . '[saleprice][from]" value="' . $options['saleprice']['from'] . '">' .
				 __('To:', 'BeRocket_domain') . '<input class="price_to"   type="number" min="1" name="' . $name . '[saleprice][to]"   value="' . $options['saleprice']['to'] . '">';
		return $html;
	}

	public static function condition_product_regularprice($html, $name, $options) {
		$def_options = ['regularprice' => ['from' => '1', 'to' => '1']];
		$options = array_merge($def_options, $options);
		if (! is_array($options['regularprice'])) {
			$options['regularprice'] = [];
		}
		$options['price'] = array_merge($def_options['regularprice'], $options['regularprice']);
		$html .= br_supcondition_equal($name, $options);
		$html .= __('From:', 'BeRocket_domain') . '<input class="price_from" type="number" min="0" name="' . $name . '[regularprice][from]" value="' . $options['regularprice']['from'] . '">' .
				 __('To:', 'BeRocket_domain') . '<input class="price_to"   type="number" min="1" name="' . $name . '[regularprice][to]"   value="' . $options['regularprice']['to'] . '">';
		return $html;
	}

	public static function condition_product_stockquantity($html, $name, $options) {
		$def_options = ['stockquantity' => '1', 'backorder' => 'any'];
		$options = array_merge($def_options, $options);
		$html .= br_supcondition_equal($name, $options, ['equal_less' => true, 'equal_more' => true]);
		$html .= __('Products in stock', 'BeRocket_domain');
		$html .= '<input type="number" min="0" name="' . $name . '[stockquantity]" value="' . $options['stockquantity'] . '">';
		$html .= '<label>' . __('Backorder allowed', 'BeRocket_domain') . ' <select name="' . $name . '[backorder]">
			<option value="any"' . ($options['backorder'] == 'any' ? ' selected' : '') . '>' . __('Any', 'BeRocket_domain') . '</option>
			<option value="yes"' . ($options['backorder'] == 'yes' ? ' selected' : '') . '>' . __('Yes', 'BeRocket_domain') . '</option>
			<option value="no"' . ($options['backorder'] == 'no' ? ' selected' : '') . '>' . __('No', 'BeRocket_domain') . '</option>
		</select></label>';
		return $html;
	}

	//SAVE PRODUCT CONDITIONS
	public static function save_condition_product($condition) {
		if (isset($condition['product']) && is_array($condition['product'])) {
			$condition['additional_product'] = [];
			foreach ($condition['product'] as $product) {
				$wc_product = wc_get_product($product);
				if ($wc_product->get_type() == 'grouped') {
					$children = $wc_product->get_children();
					if (! is_array($children)) {
						$children = [];
					}
					$condition['additional_product'] = array_merge($condition['additional_product'], $children);
				}
			}
		}
		return $condition;
	}

	//CHECK PRODUCT CONDITIONS
	public static function check_condition_product($show, $condition, $additional) {
		if (isset($condition['product']) && is_array($condition['product'])) {
			$show = in_array($additional['product_id'], $condition['product'], true);
			if (! empty($condition['additional_product']) && is_array($condition['additional_product'])) {
				$show = $show || in_array($additional['product_id'], $condition['additional_product'], true);
			}
			if ($condition['equal'] == 'not_equal') {
				$show = ! $show;
			}
		}
		return $show;
	}

	public static function check_condition_product_sale($show, $condition, $additional) {
		$show = $additional['product']->is_on_sale();
		if ($condition['sale'] == 'no') {
			$show = ! $show;
		}
		return $show;
	}

	public static function check_condition_product_bestsellers($show, $condition, $additional) {
		$args = [
			'post_type'             => 'product',
			'post_status'           => 'publish',
			'ignore_sticky_posts'   => 1,
			'posts_per_page'        => $condition['bestsellers'],
			'meta_key' 	            => 'total_sales',
			'orderby'               => 'meta_value_num',
			'meta_query'            => [
				[
					'key'       => '_visibility',
					'value'     => ['catalog', 'visible'],
					'compare'   => 'IN',
				],
			],
		];
		$posts = get_posts($args);
		if (is_array($posts)) {
			foreach ($posts as $post) {
				if ($additional['product_id'] == $post->ID) {
					$show = true;
					break;
				}
			}
		}
		return $show;
	}

	public static function check_condition_product_featured($show, $condition, $additional) {
		$show = function_exists('wc_get_product_visibility_term_ids');
		if ($show) {
			$terms_id = wc_get_product_visibility_term_ids();
			$show = ! empty($terms_id['featured']);
			if ($show) {
				$show = false;
				$terms = get_the_terms($additional['product_id'], 'product_visibility');
				if (is_array($terms)) {
					foreach ($terms as $term) {
						if ($term->term_id == $terms_id['featured']) {
							$show = true;
							break;
						}
					}
				}
			}
		}
		if ($condition['equal'] == 'not_equal') {
			$show = ! $show;
		}
		return $show;
	}

	public static function check_condition_product_shippingclass($show, $condition, $additional) {
		$terms = get_the_terms($additional['product_id'], 'product_shipping_class');
		if (is_array($terms)) {
			foreach ($terms as $term) {
				if ($term->term_id == $condition['term']) {
					$show = true;
					break;
				}
			}
		}
		if ($condition['equal'] == 'not_equal') {
			$show = ! $show;
		}
		return $show;
	}

	public static function check_condition_product_type($show, $condition, $additional) {
		$show = $additional['product']->is_type($condition['product_type']);
		if ($condition['equal'] == 'not_equal') {
			$show = ! $show;
		}
		return $show;
	}
	public static function check_condition_product_rating($show, $condition, $additional) {
		$show = ($additional['product']->get_average_rating() > 0);
		if ($condition['has_rating'] == 'no') {
			$show = ! $show;
		}
		return $show;
	}

	public static function check_condition_product_price($show, $condition, $additional) {
		$product_price = br_wc_get_product_attr($additional['product'], 'price');
		$show = self::check_any_price_for_variations($additional['product'], 'price', $condition['price']['from'], $condition['price']['to']);
		if ($condition['equal'] == 'not_equal') {
			$show = ! $show;
		}
		return $show;
	}

	public static function check_condition_product_stockstatus($show, $condition, $additional) {
		if ($condition['stockstatus'] == 'is_on_backorder') {
			$show = $additional['product']->is_on_backorder();
		} else {
			$show = $additional['product']->is_in_stock();
			if ($condition['stockstatus'] == 'out_of_stock') {
				$show = ! $show;
			}
		}
		return $show;
	}

	public static function check_condition_product_totalsales($show, $condition, $additional) {
		$total_sales = get_post_meta($additional['product_id'], 'total_sales', true);
		$show = static::supcondition_check($total_sales, $condition['totalsales'], $condition);
		return $show;
	}

	public static function check_condition_product_category($show, $condition, $additional) {
		if (! is_array($condition['category'])) {
			$condition['category'] = [$condition['category']];
		}
		$terms = get_the_terms($additional['product_id'], 'product_cat');
		if (is_array($terms)) {
			foreach ($terms as $term) {
				if (in_array($term->term_id, $condition['category'], true)) {
					$show = true;
				}
				if (! empty($condition['subcats']) && ! $show) {
					foreach ($condition['category'] as $category) {
						$show = term_is_ancestor_of($category, $term->term_id, 'product_cat');
						if ($show) {
							break;
						}
					}
				}
				if ($show) {
					break;
				}
			}
		}
		if ($condition['equal'] == 'not_equal') {
			$show = ! $show;
		}
		return $show;
	}

	public static function check_condition_product_attribute($show, $condition, $additional) {
		$terms = get_the_terms($additional['product_id'], $condition['attribute']);
		if (is_array($terms)) {
			foreach ($terms as $term) {
				if ($term->term_id == $condition['values'][$condition['attribute']]) {
					$show = true;
					break;
				}
			}
		}
		if ($condition['equal'] == 'not_equal') {
			$show = ! $show;
		}
		return $show;
	}

	public static function check_condition_product_age($show, $condition, $additional) {
		$post_date = $additional['product_post']->post_date;
		$post_date = date('Y-m-d', strtotime($post_date));
		$value = $condition['age'];
		$test_date = date('Y-m-d', strtotime("-${value} days", time()));
		$show = static::supcondition_check($test_date, $post_date, $condition);
		return $show;
	}

	public static function check_condition_product_saleprice($show, $condition, $additional) {
		$product_sale = br_wc_get_product_attr($additional['product'], 'sale_price');
		$show = self::check_any_price_for_variations($additional['product'], 'sale_price', $condition['saleprice']['from'], $condition['saleprice']['to']);
		if ($condition['equal'] == 'not_equal') {
			$show = ! $show;
		}
		return $show;
	}

	public static function check_condition_product_regularprice($show, $condition, $additional) {
		$product_sale = br_wc_get_product_attr($additional['product'], 'regular_price');
		$show = self::check_any_price_for_variations($additional['product'], 'regular_price', $condition['regularprice']['from'], $condition['regularprice']['to']);
		if ($condition['equal'] == 'not_equal') {
			$show = ! $show;
		}
		return $show;
	}

	public static function check_any_price_for_variations($product, $price_field = 'price', $price_from = 1, $price_to = 10) {
		if ($product->is_type('variable')) {
			$show = false;
			$product_variations = $product->get_available_variations();
			foreach ($product_variations as $product_variation) {
				$variation_product = new WC_Product_Variation($product_variation['variation_id']);
				$product_sale = br_wc_get_product_attr($variation_product, $price_field);
				if ($product_sale >= $price_from && $product_sale <= $price_to) {
					return true;
				}
			}
		} else {
			$product_sale = br_wc_get_product_attr($product, $price_field);
			$show = $product_sale >= $price_from && $product_sale <= $price_to;
		}
		return $show;
	}

	public static function check_condition_product_stockquantity($show, $condition, $additional) {
		$product = $additional['product'];
		if (method_exists($product, 'get_stock_quantity')) {
			$product_stock = $product->get_stock_quantity('edit');
		} else {
			$product_stock = $product->stock;
		}
		$backorder = true;
		if (! empty($condition['backorder']) && $condition['backorder'] != 'any') {
			$backorder = $additional['product']->backorders_allowed();
			if ($condition['backorder'] == 'no') {
				$backorder = ! $backorder;
			}
		}
		$show = static::supcondition_check($product_stock, $condition['stockquantity'], $condition);
		$show = $show && $backorder;
		return $show;
	}
	//PAGE CONDITIONS

	//HTML FOR PAGE CONDITIONS IN ADMIN PANEL

	public static function condition_page_id($html, $name, $options) {
		$def_options = ['pages' => []];
		$options = array_merge($def_options, $options);
		$html .= br_supcondition_equal($name, $options);
		$pages = get_pages();
		$html .= '<div style="max-height:150px;overflow:auto;border:1px solid #ccc;padding: 5px;">';
		$woo_pages = [
			'shop' => '[SHOP PAGE]',
			'product' => '[PRODUCT PAGE]',
			'category' => '[PRODUCT CATEGORY PAGE]',
			'taxonomies' => '[PRODUCT TAXONOMIES]',
			'tags' => '[PRODUCT TAGS]',
		];
		foreach ($woo_pages as $page_id => $page_name) {
			$html .= '<div><label><input name="' . $name . '[pages][]" type="checkbox" value="' . $page_id . '"' . (in_array($page_id, $options['pages'], true) ? ' checked' : '') . '>' . $page_name . '</label></div>';
		}
		foreach ($pages as $page) {
			$html .= '<div><label><input name="' . $name . '[pages][]" type="checkbox" value="' . $page->ID . '"' . (in_array($page->ID, $options['pages'], true) ? ' checked' : '') . '>' . $page->post_title . ' (ID: ' . $page->ID . ')</label></div>';
		}
		$html .= '</div>';
		return $html;
	}

	public static function condition_page_woo_attribute($html, $name, $options) {
		$def_options = ['attribute' => ''];
		$options = array_merge($def_options, $options);
		$attributes = get_object_taxonomies('product', 'objects');
		$product_attributes = [];
		foreach ($attributes as $attribute) {
			$attribute_i = [];
			$attribute_i['name'] = $attribute->name;
			$attribute_i['label'] = $attribute->label;
			$attribute_i['value'] = [];
			$terms = get_terms([
				'taxonomy' => $attribute->name,
				'hide_empty' => false,
			]);
			foreach ($terms as $term) {
				$attribute_i['value'][$term->term_id] = $term->name;
			}
			$product_attributes[] = $attribute_i;
		}
		$html .= br_supcondition_equal($name, $options);
		$html .= '<label>' . __('Select attribute', 'BeRocket_domain') . '</label>';
		$html .= '<select name="' . $name . '[attribute]" class="br_cond_attr_select">';
		$has_selected_attr = false;
		foreach ($product_attributes as $attribute) {
			$html .= '<option value="' . $attribute['name'] . '"' . (isset($options['attribute']) && $attribute['name'] == $options['attribute'] ? ' selected' : '') . '>' . $attribute['label'] . '</option>';
			if ($attribute['name'] == $options['attribute']) {
				$has_selected_attr = true;
			}
		}
		$html .= '</select>';
		$is_first_attr = ! $has_selected_attr;
		foreach ($product_attributes as $attribute) {
			$html .= '<select class="br_attr_values br_attr_value_' . $attribute['name'] . '" name="' . $name . '[values][' . $attribute['name'] . ']"' . ($is_first_attr || $attribute['name'] == $options['attribute'] ? '' : ' style="display:none;"') . '>';
			foreach ($attribute['value'] as $term_id => $term_name) {
				$html .= '<option value="' . $term_id . '"' . (! empty($options['values'][$attribute['name']]) && $options['values'][$attribute['name']] == $term_id ? ' selected' : '') . '>' . $term_name . '</option>';
			}
			$html .= '</select>';
			$is_first_attr = false;
		}
		return $html;
	}

	public static function condition_page_woo_search($html, $name, $options) {
		$def_options = ['search' => []];
		$options = array_merge($def_options, $options);
		$html .= br_supcondition_equal($name, $options);
		return $html;
	}

	public static function condition_page_woo_category($html, $name, $options) {
		$product_categories = get_terms('product_cat');
		if (is_array($product_categories) && count($product_categories) > 0) {
			$def_options = ['category' => ''];
			$options = array_merge($def_options, $options);
			$html .= br_supcondition_equal($name, $options);
			$html .= '<label><input type="checkbox" name="' . $name . '[subcats]" value="1"' . (empty($options['subcats']) ? '' : ' checked') . '>' . __('Include subcategories', 'BeRocket_products_label_domain') . '</label>';
			$html .= '<div style="max-height:150px;overflow:auto;border:1px solid #ccc;padding: 5px;">';
			foreach ($product_categories as $category) {
				$html .= '<div><label>
				<input type="checkbox" name="' . $name . '[category][]" value="' . $category->term_id . '"' . ((! empty($options['category']) && is_array($options['category']) && in_array($category->term_id, $options['category'], true)) ? ' checked' : '') . '>
				' . $category->name . '
				</label></div>';
			}
			$html .= '</div>';
		}
		return $html;
	}

	//CHECK PAGE CONDITIONS

	public static function check_condition_page_id($show, $condition, $additional) {
		$show = false;
		$def_options = ['pages' => []];
		$condition = array_merge($def_options, $condition);
		if (is_array($condition['pages']) && count($condition['pages']) != 0) {
			if (function_exists('is_shop') && function_exists('is_product_category') && function_exists('is_product')) {
				if (is_shop() && in_array('shop', $condition['pages'], true)
				|| is_product_category() && in_array('category', $condition['pages'], true)
				|| is_product() && in_array('product', $condition['pages'], true)
				|| is_product_tag() && in_array('tags', $condition['pages'], true)
				|| is_product_taxonomy() && in_array('taxonomies', $condition['pages'], true)) {
					$show = true;
				}
			}
			$remove_elements = ['shop', 'category', 'product'];
			foreach ($remove_elements as $remove_element) {
				$remove_i = array_search($remove_element, $condition['pages'], true);
				if ($remove_i !== false) {
					unset($condition['pages'][$remove_i]);
				}
			}
			if (! empty($condition['pages']) && is_page($condition['pages'])) {
				$show = true;
			}
		}
		if ($condition['equal'] == 'not_equal') {
			$show = ! $show;
		}
		return $show;
	}

	public static function check_condition_page_woo_attribute($show, $condition, $additional) {
		$show = (is_tax($condition['attribute'], $condition['values'][$condition['attribute']]));
		if ($condition['equal'] == 'not_equal') {
			$show = ! $show;
		}
		return $show;
	}

	public static function check_condition_page_woo_search($show, $condition, $additional) {
		$show = (is_search());
		if ($condition['equal'] == 'not_equal') {
			$show = ! $show;
		}
		return $show;
	}

	public static function check_condition_page_woo_category($show, $condition, $additional) {
		global $wp_query;
		$show = false;
		if (! empty($condition['category']) && ! is_array($condition['category'])) {
			$condition['category'] = [$condition['category']];
		}
		if ($wp_query->is_tax) {
			$queried_object = $wp_query->get_queried_object();
			if (! empty($condition['category'])
			&& is_array($condition['category'])
			&& is_object($queried_object)
			&& property_exists($queried_object, 'term_id')
			&& property_exists($queried_object, 'taxonomy')
			&& $queried_object->taxonomy == 'product_cat') {
				$show = in_array($queried_object->term_id, $condition['category'], true);
				if (empty($show) && ! empty($condition['subcats'])) {
					foreach ($condition['category'] as $category) {
						$show = term_is_ancestor_of($category, $queried_object, 'product_cat');
						if ($show) {
							break;
						}
					}
				}
			}
		}
		if ($condition['equal'] == 'not_equal') {
			$show = ! $show;
		}
		return $show;
	}

}
