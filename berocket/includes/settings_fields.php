<?php

class BeRocket_framework_settings_fields {

	public function __construct() {
		add_filter('berocket_framework_item_content_text', [$this, 'text'], 10, 6);
		add_filter('berocket_framework_item_content_number', [$this, 'number'], 10, 6);
		add_filter('berocket_framework_item_content_radio', [$this, 'radio'], 10, 8);
		add_filter('berocket_framework_item_content_checkbox', [$this, 'checkbox'], 10, 8);
		add_filter('berocket_framework_item_content_selectbox', [$this, 'selectbox'], 10, 6);
		add_filter('berocket_framework_item_content_textarea', [$this, 'textarea'], 10, 6);
		add_filter('berocket_framework_item_content_color', [$this, 'color'], 10, 6);
		add_filter('berocket_framework_item_content_image', [$this, 'image'], 10, 6);
		add_filter('berocket_framework_item_content_faimage', [$this, 'faimage'], 10, 6);
		add_filter('berocket_framework_item_content_fontawesome', [$this, 'fontawesome'], 10, 6);
		add_filter('berocket_framework_item_content_fa', [$this, 'fontawesome'], 10, 6);
		add_filter('berocket_framework_item_content_products', [$this, 'products'], 10, 6);
	}

	public function text($html, $field_item, $field_name, $value, $class, $extra) {
		$html .= '<label>' . $field_item['label_be_for']
		  . '<input type="text" name="' . $field_name
		  . '" value="' . htmlentities($value) . '"' . $class . $extra . '/>'
		  . $field_item['label_for'] . '</label>';
		return $html;
	}

	public function number($html, $field_item, $field_name, $value, $class, $extra) {
		$html .= '<label>' . $field_item['label_be_for']
		  . '<input type="number" name="' . $field_name
		  . '" value="' . $value . '"' . $class . $extra
		  . (empty($field_item['min']) ? '' : ' min="' . $field_item['min'] . '"') . (empty($field_item['max']) ? '' : ' max="' . $field_item['max'] . '"') . '/>'
		  . $field_item['label_for'] . '</label>';
		return $html;
	}

	public function radio($html, $field_item, $field_name, $value, $class, $extra, $option_values, $option_deault_values) {
		$radio_default = ($option_values ?? (! empty($field_item['default']) ? $field_item['value'] : (! empty($option_deault_values) ? $option_deault_values : '')));
		$html .= '<label>' . $field_item['label_be_for']
		  . '<input type="radio" name="' . $field_name
		  . '" value="' . $field_item['value'] . '"'
		  . ($field_item['value'] == $radio_default ? ' checked="checked" ' : '')
		  . $class . $extra . '>'
		  . $field_item['label_for'] . '</label>';
		return $html;
	}

	public function checkbox($html, $field_item, $field_name, $value, $class, $extra, $option_values, $option_deault_values) {
		$html .= '<label>' . $field_item['label_be_for']
		  . '<input type="checkbox" name="' . $field_name
		  . '" value="' . $field_item['value'] . '"' .
		  ((! empty($option_values)) ? ' checked="checked" ' : '') . $class . $extra . '/>'
		  . $field_item['label_for'] . '</label>';
		return $html;
	}

	public function selectbox($html, $field_item, $field_name, $value, $class, $extra) {
		$html .= '<label>' . $field_item['label_be_for']
		 . '<select name="' . $field_name
		 . '"' . $class . $extra . '>';
		if (isset($field_item['options']) && is_array($field_item['options']) && count($field_item['options'])) {
			foreach ($field_item['options'] as $option) {
				$html .= '<option value="' . $option['value'] . '"' .
					(($value == $option['value']) ? ' selected="selected" ' : '') . '>' .
					$option['text'] . '</option>';
			}
		} else {
			$html .= "<option>Options data is corrupted!</option>";
		}
		$html .= '</select>' . $field_item['label_for'] . '</label>';
		return $html;
	}

	public function textarea($html, $field_item, $field_name, $value, $class, $extra) {
		return $html . $field_item['label_be_for'] . '<textarea name="' . $field_name . '"' . $class . $extra . '>' .
			$value .
			'</textarea>' . $field_item['label_for'];
	}

	public function color($html, $field_item, $field_name, $value, $class, $extra) {
		$html .= $field_item['label_be_for'];
		if (empty($value)) {
			$value = $field_item['value'];
		}
		$html .= br_color_picker($field_name, $value, ($field_item['value'] ?? ''), $field_item);
		$html .= $field_item['label_for'];
		return $html;
	}

	public function image($html, $field_item, $field_name, $value, $class, $extra) {
		$html .= $field_item['label_be_for'];
		$html .= br_upload_image($field_name, $value, $field_item);
		$html .= $field_item['label_for'];
		return $html;
	}

	public function faimage($html, $field_item, $field_name, $value, $class, $extra) {
		$html .= $field_item['label_be_for'];
		$html .= br_fontawesome_image($field_name, $value, $field_item);
		$html .= $field_item['label_for'];
		return $html;
	}

	public function fontawesome($html, $field_item, $field_name, $value, $class, $extra) {
		$html .= $field_item['label_be_for'];
		$html .= br_select_fontawesome($field_name, $value, $field_item);
		$html .= $field_item['label_for'];
		return $html;
	}

	public function products($html, $field_item, $field_name, $value, $class, $extra) {
		$html .= $field_item['label_be_for'];
		$html .= br_products_selector($field_name, $value, $field_item);
		$html .= $field_item['label_for'];
		return $html;
	}

}

new BeRocket_framework_settings_fields();

