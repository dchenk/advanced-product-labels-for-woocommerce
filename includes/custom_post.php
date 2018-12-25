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
		'template'              => '',
		'div_custom_class'      => '',
		'div_custom_css'        => '',
		'span_custom_class'     => '',
		'span_custom_css'       => '',
		'b_custom_class'        => '',
		'b_custom_css'          => '',
		'i1_custom_class'       => '',
		'i1_custom_css'         => '',
		'i2_custom_class'       => '',
		'i2_custom_css'         => '',
		'i3_custom_class'       => '',
		'i3_custom_css'         => '',
		'i4_custom_class'       => '',
		'i4_custom_css'         => '',
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
			'label' => __('Product Labels', 'BeRocket_products_label_domain'),
			'labels' => [
				'name'               => __('Labels', 'BeRocket_products_label_domain'),
				'singular_name'      => __('Label', 'BeRocket_products_label_domain'),
				'menu_name'          => __('Product Labels', 'BeRocket_products_label_domain'),
				'add_new'            => __('Add Label', 'BeRocket_products_label_domain'),
				'add_new_item'       => __('Add New Label', 'BeRocket_products_label_domain'),
				'edit'               => __('Edit', 'BeRocket_products_label_domain'),
				'edit_item'          => __('Edit Label', 'BeRocket_products_label_domain'),
				'new_item'           => __('New Label', 'BeRocket_products_label_domain'),
				'view'               => __('View Labels', 'BeRocket_products_label_domain'),
				'view_item'          => __('View Label', 'BeRocket_products_label_domain'),
				'search_items'       => __('Search Product Labels', 'BeRocket_products_label_domain'),
				'not_found'          => __('No Labels found', 'BeRocket_products_label_domain'),
				'not_found_in_trash' => __('No Labels found in trash', 'BeRocket_products_label_domain'),
			],
			'description'         => __('Add and manage product labels.', 'BeRocket_products_label_domain'),
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

		$this->add_meta_box('conditions', __('Conditions', 'BeRocket_products_label_domain'));
		$this->add_meta_box('settings', __('Advanced Labels Settings', 'BeRocket_products_label_domain'));
		$this->add_meta_box('description', __('Description', 'BeRocket_products_label_domain'), false, 'side');
		$this->add_meta_box('preview', __('Preview', 'BeRocket_products_label_domain'), false, 'side');

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
		echo $this->conditions->build($options['data']);
	}

	public function description($post) {
		?>
        <p><?php _e('Label without any condition will be displayed on all products', 'BeRocket_products_label_domain'); ?></p>
        <p><?php _e('Connection between condition can be AND and OR', 'BeRocket_products_label_domain'); ?></p>
        <p><strong>AND</strong> <?php _e('uses between condition in one section', 'BeRocket_products_label_domain'); ?></p>
        <p><strong>OR</strong> <?php _e('uses between different sections with conditions', 'BeRocket_products_label_domain'); ?></p>
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
							['value' => 'text', 'text' => __('Text', 'BeRocket_products_label_domain')],
							['value' => 'sale_p', 'text' => __('Discount percentage', 'BeRocket_products_label_domain')],
							['value' => 'price', 'text' => __('Price', 'BeRocket_products_label_domain')],
							['value' => 'stock_status', 'text' => __('Stock Status', 'BeRocket_products_label_domain')],
						],
						"class"    => 'berocket_label_content_type',
						"label"    => __('Content type', 'BeRocket_products_label_domain'),
						"name"     => "content_type",
						"value"    => $options['content_type'],
					],
					'text' => [
						"type"     => "text",
						"label"    => __('Text', 'BeRocket_products_label_domain'),
						"class"    => 'berocket_label_ berocket_label_text',
						"name"     => "text",
						"value"    => $options['text'],
					],
					'text_before' => [
						"label"    => __('Text Before', 'BeRocket_products_label_domain'),
						"items"    => [
							'text_before' => [
								"type"     => "text",
								"class"    => 'berocket_label_ berocket_label_sale_p',
								"label_be_for" => __('Text', 'BeRocket_products_label_domain'),
								"name"     => "text_before",
								"value"    => $options['text_before'],
							],
							"text_before_nl" =>[
								"type"     => "checkbox",
								"label_for" => __('New Line', 'BeRocket_products_label_domain'),
								"name"     => "text_before_nl",
								"value"    => "1",
								"selected" => false,
							],
						],
					],
					'text_after' => [
						"label"    => __('Text After', 'BeRocket_products_label_domain'),
						"items"    => [
							'text_after' => [
								"type"     => "text",
								"class"    => 'berocket_label_ berocket_label_sale_p',
								"label_be_for" => __('Text', 'BeRocket_products_label_domain'),
								"name"     => "text_after",
								"value"    => $options['text_after'],
							],
							"text_before_nl" =>[
								"type"     => "checkbox",
								"label_for" => __('New Line', 'BeRocket_products_label_domain'),
								"name"     => "text_after_nl",
								"value"    => "1",
								"selected" => false,
							],
						],
					],
					'discount_minus' => [
						"type"     => "checkbox",
						"label"    => __('Use minus symbol', 'BeRocket_products_label_domain'),
						"class"    => 'berocket_label_ berocket_label_sale_p',
						"name"     => "discount_minus",
						"value"    => "1",
						"selected" => false,
					],
				],
				'Style'     => [
					/*'templates' => array(
						"section"  => "templates",
						"label"    => __('Templates', 'BeRocket_products_label_domain'),
						"name"     => "css_template",
						"value"    => $options['template'],
					),*/
					'color_use' => [
						"type"     => "checkbox",
						"label"    => __('Use background color', 'BeRocket_products_label_domain'),
						"class"    => 'br_label_backcolor_use br_js_change',
						"name"     => "color_use",
						"value"    => "1",
						"extra"    => ' data-for=".br_alabel > span" data-style="use:background-color" data-ext=""',
						"selected" => false,
					],
					'color' => [
						"type"     => "color",
						"label"    => __('Background color', 'BeRocket_products_label_domain'),
						"name"     => "color",
						"class"    => 'br_label_backcolor br_js_change',
						"extra"    => ' data-for=".br_alabel > span" data-style="background-color" data-ext=""',
						"value"    => $options['color'],
					],
					'font_color' => [
						"type"     => "color",
						"label"    => __('Font color', 'BeRocket_products_label_domain'),
						"name"     => "font_color",
						"class"    => 'berocket_label_ berocket_label_text berocket_label_sale_end berocket_label_sale_p br_js_change',
						"extra"    => ' data-for=".br_alabel > span" data-style="color" data-ext=""',
						"value"    => $options['font_color'],
					],
					'border_radius' => [
						"type"     => "text",
						"label"    => __('Border radius', 'BeRocket_products_label_domain'),
						"name"     => "border_radius",
						"class"    => "br_js_change",
						"extra"    => ' data-for=".br_alabel > span" data-style="border-radius" data-ext="px" data-notext="px,em,%"',
						"value"    => '10',
					],
					'line_height' => [
						"type"     => "number",
						"label"    => __('Line height', 'BeRocket_products_label_domain'),
						"name"     => "line_height",
						"class"    => "br_js_change",
						"extra"    => ' min="0" max="400" data-for=".br_alabel > span" data-style="line-height" data-ext="px"',
						"value"    => $options['line_height'],
					],
					'image_height' => [
						"type"     => "number",
						"label"    => __('Height', 'BeRocket_products_label_domain'),
						"name"     => "image_height",
						"class"    => "br_js_change",
						"extra"    => ' data-for=".br_alabel > span" data-style="height" data-ext="px"',
						"value"    => $options['image_height'],
					],
					'image_width' => [
						"type"     => "number",
						"label"    => __('Width', 'BeRocket_products_label_domain'),
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
							['value' => 'label', 'text' => __('Label', 'BeRocket_products_label_domain')],
							['value' => 'image', 'text' => __('On image', 'BeRocket_products_label_domain')],
						],
						"class"    => 'berocket_label_type_select',
						"label"    => __('Type', 'BeRocket_products_label_domain'),
						"name"     => "type",
						"value"    => $options['type'],
					],
					'padding_top' => [
						"type"     => "number",
						"label"    => __('Padding from top', 'BeRocket_products_label_domain'),
						"class"    => 'berocket_label_type_ berocket_label_type_image br_js_change',
						"name"     => "padding_top",
						"extra"    => ' data-for=".br_alabel" data-style="top" data-ext="px"',
						"value"    => $options['padding_top'],
					],
					'padding_horizontal' => [
						"type"     => "number",
						"label"    => '<span class="pos__ pos__left">' . __('Padding from left: ', 'BeRocket_products_label_domain') . '</span><span class="pos__ pos__right">' . __('Padding from right: ', 'BeRocket_products_label_domain') . '</span>',
						"class"    => 'berocket_label_type_ berocket_label_type_image pos_label_ pos_label_right pos_label_left br_js_change',
						"name"     => "padding_horizontal",
						"extra"    => ' data-for=".br_alabel" data-from=".pos_label" data-ext="px"',
						"value"    => $options['padding_horizontal'],
					],
					'position' => [
						"type"     => "selectbox",
						"options"  => [
							['value' => 'left', 'text' => __('Left', 'BeRocket_products_label_domain')],
							['value' => 'center', 'text' => __('Center', 'BeRocket_products_label_domain')],
							['value' => 'right', 'text' => __('Right', 'BeRocket_products_label_domain')],
						],
						"class"    => 'pos_label',
						"label"    => __('Position', 'BeRocket_products_label_domain'),
						"name"     => "position",
						"value"    => $options['position'],
					],
				],
				'Tooltip'   => [
					'tooltip_content' => [
						'label'    => __('Content', 'BeRocket_products_label_domain'),
						"type"     => "textarea",
						"class"    => "berocket_html_tooltip_content",
						"name"     => "tooltip_content",
						"value"    => $options['tooltip_content'],
					],
					'tooltip_theme' => [
						"type"     => "selectbox",
						"options"  => [
							['value' => 'dark', 'text' => __('Dark', 'BeRocket_products_label_domain')],
							['value' => 'light', 'text' => __('Light', 'BeRocket_products_label_domain')],
							['value' => 'translucent', 'text' => __('Translucent', 'BeRocket_products_label_domain')],
						],
						"label"    => __('Style', 'BeRocket_products_label_domain'),
						"name"     => "tooltip_theme",
						"value"    => $options['tooltip_theme'],
					],
					'tooltip_position' => [
						"type"     => "selectbox",
						"options"  => [
							['value' => 'top', 'text' => __('Top', 'BeRocket_products_label_domain')],
							['value' => 'bottom', 'text' => __('Bottom', 'BeRocket_products_label_domain')],
							['value' => 'left', 'text' => __('Left', 'BeRocket_products_label_domain')],
							['value' => 'right', 'text' => __('Right', 'BeRocket_products_label_domain')],
						],
						"label"    => __('Position', 'BeRocket_products_label_domain'),
						"name"     => "tooltip_position",
						"value"    => $options['tooltip_position'],
					],
					'tooltip_open_delay' => [
						"type"     => "number",
						"label"    => __('Open delay', 'BeRocket_products_label_domain'),
						"name"     => "tooltip_open_delay",
						"extra"    => 'min="0"',
						"value"    => $options['tooltip_open_delay'],
					],
					'tooltip_close_delay' => [
						"type"     => "number",
						"label"    => __('Close delay', 'BeRocket_products_label_domain'),
						"name"     => "tooltip_close_delay",
						"extra"    => 'min="0"',
						"value"    => $options['tooltip_close_delay'],
					],
					'tooltip_open_on' => [
						"type"     => "selectbox",
						"options"  => [
							['value' => 'mouseenter', 'text' => __('Hover', 'BeRocket_products_label_domain')],
							['value' => 'click', 'text' => __('Click', 'BeRocket_products_label_domain')],
						],
						"label"    => __('Open on', 'BeRocket_products_label_domain'),
						"name"     => "tooltip_open_on",
						"value"    => $options['tooltip_open_on'],
					],
					'tooltip_close_on_click' => [
						"type"     => "checkbox",
						"label"    => __('Close on click everywhere', 'BeRocket_products_label_domain'),
						"name"     => "tooltip_close_on_click",
						"value"    => '1',
					],
					'tooltip_use_arrow' => [
						"type"     => "checkbox",
						"label"    => __('Use arrow', 'BeRocket_products_label_domain'),
						"name"     => "tooltip_use_arrow",
						"value"    => '1',
					],
					'tooltip_max_width' => [
						"type"     => "number",
						"label"    => __('Max width', 'BeRocket_products_label_domain'),
						"name"     => "tooltip_max_width",
						"extra"    => 'min="0"',
						"value"    => $options['tooltip_max_width'],
					],
				],
				'Custom CSS' => [
					'div_custom_class' => [
						"type"     => "text",
						"label"    => __('&lt;div&gt; block custom class', 'BeRocket_products_label_domain'),
						"name"     => "div_custom_class",
						"value"    => $options['div_custom_class'],
					],
					'div_custom_css' => [
						"type"     => "textarea",
						"label"    => __('&lt;div&gt; block custom CSS', 'BeRocket_products_label_domain'),
						"name"     => "div_custom_css",
						"value"    => $options['div_custom_css'],
					],
					'span_custom_class' => [
						"type"     => "text",
						"label"    => __('&lt;span&gt; block custom class', 'BeRocket_products_label_domain'),
						"name"     => "span_custom_class",
						"value"    => $options['span_custom_class'],
					],
					'span_custom_css' => [
						"type"     => "textarea",
						"label"    => __('&lt;span&gt; block custom CSS', 'BeRocket_products_label_domain'),
						"name"     => "span_custom_css",
						"value"    => $options['span_custom_css'],
					],
					'b_custom_class' => [
						"type"     => "text",
						"label"    => __('&lt;b&gt; block custom class', 'BeRocket_products_label_domain'),
						"name"     => "b_custom_class",
						"value"    => $options['b_custom_class'],
					],
					'b_custom_css' => [
						"type"     => "textarea",
						"label"    => __('&lt;b&gt; block custom CSS', 'BeRocket_products_label_domain'),
						"name"     => "b_custom_css",
						"value"    => $options['b_custom_css'],
					],
					'i1_custom_class' => [
						"type"     => "text",
						"label"    => __('1) &lt;i&gt; block custom class', 'BeRocket_products_label_domain'),
						"name"     => "i1_custom_class",
						"value"    => $options['i1_custom_class'],
					],
					'i1_custom_css' => [
						"type"     => "textarea",
						"label"    => __('1) &lt;i&gt; block custom CSS', 'BeRocket_products_label_domain'),
						"name"     => "i1_custom_css",
						"value"    => $options['i1_custom_css'],
					],
					'i2_custom_class' => [
						"type"     => "text",
						"label"    => __('2) &lt;i&gt; block custom class', 'BeRocket_products_label_domain'),
						"name"     => "i2_custom_class",
						"value"    => $options['i2_custom_class'],
					],
					'i2_custom_css' => [
						"type"     => "textarea",
						"label"    => __('2) &lt;i&gt; block custom CSS', 'BeRocket_products_label_domain'),
						"name"     => "i2_custom_css",
						"value"    => $options['i2_custom_css'],
					],
					'i3_custom_class' => [
						"type"     => "text",
						"label"    => __('3) &lt;i&gt; block custom class', 'BeRocket_products_label_domain'),
						"name"     => "i3_custom_class",
						"value"    => $options['i3_custom_class'],
					],
					'i3_custom_css' => [
						"type"     => "textarea",
						"label"    => __('3) &lt;i&gt; block custom CSS', 'BeRocket_products_label_domain'),
						"name"     => "i3_custom_css",
						"value"    => $options['i3_custom_css'],
					],
					'i4_custom_class' => [
						"type"     => "text",
						"label"    => __('4) &lt;i&gt; block custom class', 'BeRocket_products_label_domain'),
						"name"     => "i4_custom_class",
						"value"    => $options['i4_custom_class'],
					],
					'i4_custom_css' => [
						"type"     => "textarea",
						"label"    => __('4) &lt;i&gt; block custom CSS', 'BeRocket_products_label_domain'),
						"name"     => "i4_custom_css",
						"value"    => $options['i4_custom_css'],
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
		if (empty($_REQUEST[$this->post_name . '_nonce']) || ! wp_verify_nonce($_REQUEST[$this->post_name . '_nonce'], $this->post_name . '_check')) {
			return false;
		}
		return true;
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
		$columns["products"] = __("Label text", 'BeRocket_products_label_domain');
		$columns["data"] = __("Position", 'BeRocket_products_label_domain');
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
				$text = __('Discount percentage', 'BeRocket_products_label_domain');
			}
			echo apply_filters('berocket_labels_products_column_text', $text, $label_type);
			break;
		case 'data':
			$position = ['left' => __('Left', 'BeRocket_products_label_domain'), 'center' => __('Center', 'BeRocket_products_label_domain'), 'right' => __('Right', 'BeRocket_products_label_domain')];
			$type = ['image' => __('On image', 'BeRocket_products_label_domain'), 'label' => __('Label', 'BeRocket_products_label_domain')];
			if (isset($label_type['position'], $label_type['type'])) {
				echo $type[$label_type['type']] . ' ( ' . $position[$label_type['position']] . ' )';
			}
		}
	}
}

new BeRocket_advanced_labels_custom_post();
