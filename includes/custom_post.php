<?php

class BeRocket_advanced_labels_custom_post extends BeRocket_custom_post_class {

	public $post_name = 'br_labels';
	public $hook_name = 'berocket_advanced_label_editor';
	public $default_settings = [
		'label_from_post'       => '',
		'content_type'          => 'text',
		'text'                  => 'Label',
		'text_before'           => '',
		'text_before_nl'        => '',
		'text_after'            => '',
		'text_after_nl'         => '',
		'image'                 => '',
		'type'                  => 'label',
		'padding_top'           => '-10',
		'padding_horizontal'    => '0',
		'border_radius'         => '3',
		'border_width'          => '0',
		'border_color'          => 'ffffff',
		'image_height'          => '30',
		'image_width'           => '50',
		'color_use'             => '1',
		'color'                 => 'f16543',
		'font_color'            => 'ffffff',
		'font_size'             => '14',
		'line_height'           => '30',
		'position'              => 'left',
		'rotate'                => '0deg',
		'zindex'                => '500',
		'data'                  => [],
		'tooltip_content'       => '',
		'tooltip_theme'         => 'dark',
		'tooltip_position'      => 'top',
		'tooltip_open_delay'    => '0',
		'tooltip_close_delay'   => '0',
		'tooltip_open_on'       => 'click',
		'tooltip_close_on_click'=> '0',
		'tooltip_use_arrow'     => '0',
		'tooltip_max_width'     => '300',
		'div_custom_class'      => '',
		'div_custom_css'        => '',
		'span_custom_class'     => '',
		'span_custom_css'       => '',
		'b_custom_class'        => '',
		'b_custom_css'          => '',
		'i1_custom_class'       => '',
		'i2_custom_class'       => '',
		'i3_custom_class'       => '',
		'i4_custom_class'       => '',
	];

	/**
	 * @var BeRocket_conditions
	 */
	public $conditions;

	/**
	 * @var BeRocket_advanced_labels_custom_post
	 */
	protected static $instance;

	public function __construct() {
		$this->post_settings = [
			'label' => __('Product Labels', 'advanced_product_labels'),
			'labels' => [
				'name'               => __('Labels', 'advanced_product_labels'),
				'singular_name'      => __('Label', 'advanced_product_labels'),
				'menu_name'          => __('Product Labels', 'advanced_product_labels'),
				'add_new'            => __('Add Label', 'advanced_product_labels'),
				'add_new_item'       => __('Add New Label', 'advanced_product_labels'),
				'edit'               => __('Edit', 'advanced_product_labels'),
				'edit_item'          => __('Edit Label', 'advanced_product_labels'),
				'new_item'           => __('New Label', 'advanced_product_labels'),
				'view'               => __('View Labels', 'advanced_product_labels'),
				'view_item'          => __('View Label', 'advanced_product_labels'),
				'search_items'       => __('Search Product Labels', 'advanced_product_labels'),
				'not_found'          => __('No Labels found', 'advanced_product_labels'),
				'not_found_in_trash' => __('No Labels found in trash', 'advanced_product_labels'),
			],
			'description'         => __('Add and manage product labels.', 'advanced_product_labels'),
			'public'              => true,
			'show_ui'             => true,
			'map_meta_cap'        => true,
			'capability_type'     => 'product',
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_menu'        => true,
			'hierarchical'        => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => ['title'],
			'show_in_nav_menus'   => false,
		];

		parent::__construct();

		$this->add_meta_box('conditions', __('Conditions', 'advanced_product_labels'));
		$this->add_meta_box('settings', __('Advanced Labels Settings', 'advanced_product_labels'));
		$this->add_meta_box('description', __('Description', 'advanced_product_labels'), false, 'side');
		$this->add_meta_box('preview', __('Preview', 'advanced_product_labels'), false, 'side');

		$this->conditions = new BeRocket_conditions($this->post_name . '[data]', $this->hook_name, [
			'condition_product',
			'condition_product_category',
			'condition_product_sale',
			'condition_product_bestsellers',
			'condition_product_price',
			'condition_product_stockstatus',
			'condition_product_totalsales',
			'condition_product_featured',
			'condition_product_age',
			'condition_product_type',
			'condition_product_rating',
		]);
	}

	public function admin_init() {
		parent::admin_init();
		add_filter('manage_edit-' . $this->post_name . '_columns', [$this, 'manage_edit_columns']);
		add_action('manage_' . $this->post_name . '_posts_custom_column', [$this, 'columns_replace'], 10, 2);
	}

	public function conditions($post) {
		$options = $this->get_option($post->ID);
		if (empty($options['data'])) {
			$options['data'] = [];
		}
		$this->conditions->build($options['data']);
	}

	public function description($post) {
		?>
        <p><?php _e('Label without any condition will be displayed on all products', 'advanced_product_labels'); ?></p>
        <p><?php _e('Connection between condition can be AND and OR', 'advanced_product_labels'); ?></p>
        <p><strong>AND</strong> <?php _e('uses between condition in one section', 'advanced_product_labels'); ?></p>
        <p><strong>OR</strong> <?php _e('uses between different sections with conditions', 'advanced_product_labels'); ?></p>
        <?php
	}

	public function preview($post) {
		wp_enqueue_style('berocket_tippy');
		wp_enqueue_script('berocket_tippy'); ?>
        <div class="berocket_label_preview_wrap">
            <div class="berocket_label_preview">
                <img class="berocket_product_image" src="<?php echo BeRocket_products_label::getInstance()->plugin_url() . 'images/labels.png'; ?>">
            </div>
        </div>
        <style>
            div.berocket_label_preview_wrap {
                display: inline-block;
                width: 240px;
                padding: 20px;
                background: white;
                position: relative;
                top: 0;
                margin-top: 0;
                min-height: 320px;
                right: 0;
                box-sizing: border-box;
            }
            .berocket_label_preview_wrap .berocket_label_preview {
                position: relative;
            }
            .berocket_label_preview_wrap .berocket_product_image {
                display: block;
                width: 200px;
            }
            .postbox#preview {
                overflow:hidden;
            }
        </style>
        <?php
	}

	public function settings(WP_Post $post) {
		$inst = BeRocket_products_label::getInstance();
		wp_enqueue_script('berocket_products_label_admin', $inst->plugin_url() . 'js/admin.js', ['jquery'], BeRocket_products_label_version);
		wp_enqueue_script('berocket_framework_admin');
		wp_enqueue_style('berocket_framework_admin_style');
		wp_enqueue_script('berocket_widget-colorpicker');
		wp_enqueue_style('berocket_widget-colorpicker-style');
		wp_enqueue_style('berocket_font_awesome');
		$options = $this->get_option($post->ID);
		$BeRocket_products_label_var = BeRocket_products_label::getInstance();
		echo '<div class="br_framework_settings br_alabel_settings">';
		$BeRocket_products_label_var->display_admin_settings(
			[
				'General' => [
					'icon' => 'cog',
				],
				'Style'     => [
					'icon' => 'css3',
				],
				'Position'     => [
					'icon' => 'arrows',
				],
				'Tooltip'     => [
					'icon' => 'comment',
				],
				'Custom CSS'   => [
					'icon' => 'css3',
				],
			],
			[
				'General' => [
					'content_type' => [
						"type"     => "selectbox",
						"options"  => [
							['value' => 'text', 'text' => __('Text', 'advanced_product_labels')],
							['value' => 'sale_p', 'text' => __('Discount percentage', 'advanced_product_labels')],
							['value' => 'price', 'text' => __('Price', 'advanced_product_labels')],
							['value' => 'stock_status', 'text' => __('Stock Status', 'advanced_product_labels')],
						],
						"class"    => 'berocket_label_content_type',
						"label"    => __('Content type', 'advanced_product_labels'),
						"name"     => "content_type",
						"value"    => $options['content_type'],
					],
					'text' => [
						"type"     => "text",
						"label"    => __('Text', 'advanced_product_labels'),
						"class"    => 'berocket_label_ berocket_label_text',
						"name"     => "text",
						"value"    => $options['text'],
					],
					'text_before' => [
						"label"    => __('Text Before', 'advanced_product_labels'),
						"items"    => [
							'text_before' => [
								"type"     => "text",
								"class"    => 'berocket_label_ berocket_label_sale_p',
								"label_be_for" => __('Text', 'advanced_product_labels'),
								"name"     => "text_before",
								"value"    => $options['text_before'],
							],
							"text_before_nl" =>[
								"type"     => "checkbox",
								"label_for" => __('New Line', 'advanced_product_labels'),
								"name"     => "text_before_nl",
								"value"    => "1",
								"selected" => false,
							],
						],
					],
					'text_after' => [
						"label"    => __('Text After', 'advanced_product_labels'),
						"items"    => [
							'text_after' => [
								"type"     => "text",
								"class"    => 'berocket_label_ berocket_label_sale_p',
								"label_be_for" => __('Text', 'advanced_product_labels'),
								"name"     => "text_after",
								"value"    => $options['text_after'],
							],
							"text_before_nl" =>[
								"type"     => "checkbox",
								"label_for" => __('New Line', 'advanced_product_labels'),
								"name"     => "text_after_nl",
								"value"    => "1",
								"selected" => false,
							],
						],
					],
					'discount_minus' => [
						"type"     => "checkbox",
						"label"    => __('Use minus symbol', 'advanced_product_labels'),
						"class"    => 'berocket_label_ berocket_label_sale_p',
						"name"     => "discount_minus",
						"value"    => "1",
						"selected" => false,
					],
				],
				'Style'     => [
					'color_use' => [
						"type"     => "checkbox",
						"label"    => __('Use background color', 'advanced_product_labels'),
						"class"    => 'br_label_backcolor_use br_js_change',
						"name"     => "color_use",
						"value"    => "1",
						"extra"    => ' data-for=".br_alabel > span" data-style="use:background-color" data-ext=""',
						"selected" => false,
					],
					'color' => [
						"type"     => "color",
						"label"    => __('Background color', 'advanced_product_labels'),
						"name"     => "color",
						"class"    => 'br_label_backcolor br_js_change',
						"extra"    => ' data-for=".br_alabel > span" data-style="background-color" data-ext=""',
						"value"    => $options['color'],
					],
					'font_color' => [
						"type"     => "color",
						"label"    => __('Font color', 'advanced_product_labels'),
						"name"     => "font_color",
						"class"    => 'berocket_label_ berocket_label_text berocket_label_sale_end berocket_label_sale_p br_js_change',
						"extra"    => ' data-for=".br_alabel > span" data-style="color" data-ext=""',
						"value"    => $options['font_color'],
					],
					'border_radius' => [
						"type"     => "text",
						"label"    => __('Border radius', 'advanced_product_labels'),
						"name"     => "border_radius",
						"class"    => "br_js_change",
						"extra"    => ' data-for=".br_alabel > span" data-style="border-radius" data-ext="px" data-notext="px,em,%"',
						"value"    => '10',
					],
					'line_height' => [
						"type"     => "number",
						"label"    => __('Line height', 'advanced_product_labels'),
						"name"     => "line_height",
						"class"    => "br_js_change",
						"extra"    => ' min="0" max="400" data-for=".br_alabel > span" data-style="line-height" data-ext="px"',
						"value"    => $options['line_height'],
					],
					'image_height' => [
						"type"     => "number",
						"label"    => __('Height', 'advanced_product_labels'),
						"name"     => "image_height",
						"class"    => "br_js_change",
						"extra"    => ' data-for=".br_alabel > span" data-style="height" data-ext="px"',
						"value"    => $options['image_height'],
					],
					'image_width' => [
						"type"     => "number",
						"label"    => __('Width', 'advanced_product_labels'),
						"name"     => "image_width",
						"class"    => "br_js_change",
						"extra"    => ' data-for=".br_alabel > span" data-style="width" data-ext="px"',
						"value"    => $options['image_width'],
					],
				],
				'Position'     => [
					'type' => [
						"type"     => "selectbox",
						"options"  => [
							['value' => 'label', 'text' => __('Label', 'advanced_product_labels')],
							['value' => 'image', 'text' => __('On image', 'advanced_product_labels')],
						],
						"class"    => 'berocket_label_type_select',
						"label"    => __('Type', 'advanced_product_labels'),
						"name"     => "type",
						"value"    => $options['type'],
					],
					'padding_top' => [
						"type"     => "number",
						"label"    => __('Padding from top', 'advanced_product_labels'),
						"class"    => 'berocket_label_type_ berocket_label_type_image br_js_change',
						"name"     => "padding_top",
						"extra"    => ' data-for=".br_alabel" data-style="top" data-ext="px"',
						"value"    => $options['padding_top'],
					],
					'padding_horizontal' => [
						"type"     => "number",
						"label"    => '<span class="pos__ pos__left">' . __('Padding from left: ', 'advanced_product_labels') . '</span><span class="pos__ pos__right">' . __('Padding from right: ', 'advanced_product_labels') . '</span>',
						"class"    => 'berocket_label_type_ berocket_label_type_image pos_label_ pos_label_right pos_label_left br_js_change',
						"name"     => "padding_horizontal",
						"extra"    => ' data-for=".br_alabel" data-from=".pos_label" data-ext="px"',
						"value"    => $options['padding_horizontal'],
					],
					'position' => [
						"type"     => "selectbox",
						"options"  => [
							['value' => 'left', 'text' => __('Left', 'advanced_product_labels')],
							['value' => 'center', 'text' => __('Center', 'advanced_product_labels')],
							['value' => 'right', 'text' => __('Right', 'advanced_product_labels')],
						],
						"class"    => 'pos_label',
						"label"    => __('Position', 'advanced_product_labels'),
						"name"     => "position",
						"value"    => $options['position'],
					],
				],
				'Tooltip'   => [
					'tooltip_content' => [
						'label'    => __('Content', 'advanced_product_labels'),
						"type"     => "textarea",
						"class"    => "berocket_html_tooltip_content",
						"name"     => "tooltip_content",
						"value"    => $options['tooltip_content'],
					],
					'tooltip_theme' => [
						"type"     => "selectbox",
						"options"  => [
							['value' => 'dark', 'text' => __('Dark', 'advanced_product_labels')],
							['value' => 'light', 'text' => __('Light', 'advanced_product_labels')],
							['value' => 'translucent', 'text' => __('Translucent', 'advanced_product_labels')],
						],
						"label"    => __('Style', 'advanced_product_labels'),
						"name"     => "tooltip_theme",
						"value"    => $options['tooltip_theme'],
					],
					'tooltip_position' => [
						"type"     => "selectbox",
						"options"  => [
							['value' => 'top', 'text' => __('Top', 'advanced_product_labels')],
							['value' => 'bottom', 'text' => __('Bottom', 'advanced_product_labels')],
							['value' => 'left', 'text' => __('Left', 'advanced_product_labels')],
							['value' => 'right', 'text' => __('Right', 'advanced_product_labels')],
						],
						"label"    => __('Position', 'advanced_product_labels'),
						"name"     => "tooltip_position",
						"value"    => $options['tooltip_position'],
					],
					'tooltip_open_delay' => [
						"type"     => "number",
						"label"    => __('Open delay', 'advanced_product_labels'),
						"name"     => "tooltip_open_delay",
						"extra"    => 'min="0"',
						"value"    => $options['tooltip_open_delay'],
					],
					'tooltip_close_delay' => [
						"type"     => "number",
						"label"    => __('Close delay', 'advanced_product_labels'),
						"name"     => "tooltip_close_delay",
						"extra"    => 'min="0"',
						"value"    => $options['tooltip_close_delay'],
					],
					'tooltip_open_on' => [
						"type"     => "selectbox",
						"options"  => [
							['value' => 'mouseenter', 'text' => __('Hover', 'advanced_product_labels')],
							['value' => 'click', 'text' => __('Click', 'advanced_product_labels')],
						],
						"label"    => __('Open on', 'advanced_product_labels'),
						"name"     => "tooltip_open_on",
						"value"    => $options['tooltip_open_on'],
					],
					'tooltip_close_on_click' => [
						"type"     => "checkbox",
						"label"    => __('Close on click everywhere', 'advanced_product_labels'),
						"name"     => "tooltip_close_on_click",
						"value"    => '1',
					],
					'tooltip_use_arrow' => [
						"type"     => "checkbox",
						"label"    => __('Use arrow', 'advanced_product_labels'),
						"name"     => "tooltip_use_arrow",
						"value"    => '1',
					],
					'tooltip_max_width' => [
						"type"     => "number",
						"label"    => __('Max width', 'advanced_product_labels'),
						"name"     => "tooltip_max_width",
						"extra"    => 'min="0"',
						"value"    => $options['tooltip_max_width'],
					],
				],
				'Custom CSS' => [
					'div_custom_class' => [
						"type"     => "text",
						"label"    => __('&lt;div&gt; block custom class', 'advanced_product_labels'),
						"name"     => "div_custom_class",
						"value"    => $options['div_custom_class'],
					],
					'div_custom_css' => [
						"type"     => "textarea",
						"label"    => __('&lt;div&gt; block custom CSS', 'advanced_product_labels'),
						"name"     => "div_custom_css",
						"value"    => $options['div_custom_css'],
					],
					'span_custom_class' => [
						"type"     => "text",
						"label"    => __('&lt;span&gt; block custom class', 'advanced_product_labels'),
						"name"     => "span_custom_class",
						"value"    => $options['span_custom_class'],
					],
					'span_custom_css' => [
						"type"     => "textarea",
						"label"    => __('&lt;span&gt; block custom CSS', 'advanced_product_labels'),
						"name"     => "span_custom_css",
						"value"    => $options['span_custom_css'],
					],
					'b_custom_class' => [
						"type"     => "text",
						"label"    => __('&lt;b&gt; block custom class', 'advanced_product_labels'),
						"name"     => "b_custom_class",
						"value"    => $options['b_custom_class'],
					],
					'b_custom_css' => [
						"type"     => "textarea",
						"label"    => __('&lt;b&gt; block custom CSS', 'advanced_product_labels'),
						"name"     => "b_custom_css",
						"value"    => $options['b_custom_css'],
					],
					'i1_custom_class' => [
						"type"     => "text",
						"label"    => __('First &lt;i&gt; block custom class', 'advanced_product_labels'),
						"name"     => "i1_custom_class",
						"value"    => $options['i1_custom_class'],
					],
					'i2_custom_class' => [
						"type"     => "text",
						"label"    => __('Second &lt;i&gt; block custom class', 'advanced_product_labels'),
						"name"     => "i2_custom_class",
						"value"    => $options['i2_custom_class'],
					],
					'i3_custom_class' => [
						"type"     => "text",
						"label"    => __('Third &lt;i&gt; block custom class', 'advanced_product_labels'),
						"name"     => "i3_custom_class",
						"value"    => $options['i3_custom_class'],
					],
					'i4_custom_class' => [
						"type"     => "text",
						"label"    => __('Fourth &lt;i&gt; block custom class', 'advanced_product_labels'),
						"name"     => "i4_custom_class",
						"value"    => $options['i4_custom_class'],
					],
				],
			],
			[
				'name_for_filters' => $this->hook_name,
				'hide_form' => true,
				'settings_name' => $this->post_name,
				'options' => $options,
			]
		);
		echo '</div>';
	}

	public function wc_save_check($post_id, $post): bool {
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return false;
		}
		if ($this->post_name != $post->post_type && 'product' != $post->post_type) {
			return false;
		}
		return !empty($_REQUEST[$this->post_name . '_nonce']) && wp_verify_nonce($_REQUEST[$this->post_name . '_nonce'], $this->post_name . '_check');
	}

	public function wc_save_product($post_id, $post) {
		$current_settings = get_post_meta($post_id, $this->post_name, true);
		if (empty($current_settings)) {
			update_post_meta($post_id, $this->post_name, $this->default_settings);
		}
		if (! $this->wc_save_check($post_id, $post)) {
			return;
		}
		if (! isset($_POST['br_labels']['color_use'])) {
			$_POST['br_labels']['color_use'] = 0;
		}
		$_POST['br_labels'] = apply_filters('berocket_apl_wc_save_product', $_POST['br_labels'], $post_id);
		parent::wc_save_product($post_id, $post);
	}

	public function manage_edit_columns($columns) {
		unset($columns['date']);
		$columns["products"] = __("Label text", 'advanced_product_labels');
		$columns["data"] = __("Position", 'advanced_product_labels');
		return $columns;
	}

	/**
	 * @param $column string
	 * @param $postID int
	 */
	public function columns_replace($column, $postID) {
		$label_type = $this->get_option($postID);
		switch ($column) {
		case 'products':
			$text = '';
			if (isset($label_type['text'])) {
				$text = $label_type['text'];
			}
			if ($label_type['content_type'] == 'sale_p') {
				$text = __('Discount percentage', 'advanced_product_labels');
			}
			echo apply_filters('berocket_labels_products_column_text', $text, $label_type);
			break;
		case 'data':
			$position = ['left' => __('Left', 'advanced_product_labels'), 'center' => __('Center', 'advanced_product_labels'), 'right' => __('Right', 'advanced_product_labels')];
			$type = ['image' => __('On image', 'advanced_product_labels'), 'label' => __('Label', 'advanced_product_labels')];
			if (isset($label_type['position'], $label_type['type'])) {
				echo $type[$label_type['type']] . ' ( ' . $position[$label_type['position']] . ' )';
			}
		}
	}
}

new BeRocket_advanced_labels_custom_post();
