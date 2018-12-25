<?php

class BeRocket_Labels_compat_product_preview {

	public function __construct() {
		add_action('berocket_pp_popup_inside_image', [$this, 'show_labels']);
		add_action('berocket_pp_popup_inside_thumbnails', [$this, 'show_labels']);
		add_action('BeRocket_preview_after_general_settings', [$this, 'settings'], 10, 2);
	}

	public static function show_labels($options) {
		if (empty($options['hide_berocket_labels'])) {
			$BeRocket_products_label = BeRocket_products_label::getInstance();
			$BeRocket_products_label->set_all_label();
		}
	}

	public static function settings($name, $options) {
		?>
		<tr>
			<th><?php _e('Hide Advanced Labels', 'BeRocket_products_label_domain'); ?></th>
			<td>
				<input type="checkbox" name="<?php echo $name; ?>[hide_berocket_labels]"<?php checked(!empty($options['hide_berocket_labels'])); ?>>
			</td>
		</tr><?php
	}

}

new BeRocket_Labels_compat_product_preview();

