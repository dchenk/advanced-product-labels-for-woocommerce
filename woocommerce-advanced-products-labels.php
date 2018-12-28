<?php
/**
 * Plugin Name: WooCommerce Advanced Product Labels
 * Plugin URI: https://github.com/dchenk/advanced-product-labels-for-woocommerce
 * Description: Promote your products! Show “Free Shipping” or other special attributes with your products.
 * Version: 1.1.11
 * Author: widerwebs
 * Requires at least: 4.0
 * Author URI: https://github.com/dchenk
 * Text Domain: apl_products_label_domain
 * Domain Path: /languages
 * WC tested up to: 3.4.6
 */

define('BeRocket_products_label_version', '1.1.11');

require_once(__DIR__ . '/berocket/framework.php');

require_once(__DIR__ . '/includes/compatibility/product_preview.php');
require_once(__DIR__ . '/includes/custom_post.php');

class BeRocket_products_label extends BeRocket_Framework {
	protected const options_page = 'br_products_label';
	public static $settings_name = 'br-products_label-options';

	/**
	 * @var array Metadata about the plugin
	 */
	public $info = [
		'name'        => '',
		'plugin_name' => 'products_label',
		'norm_name'   => 'Product Labels',
		'templates'   => __DIR__ . '/templates/',
		'plugin_file' => __FILE__,
	];

	public $defaults = [
		'disable_labels'               => '0',
		'disable_plabels'              => '0',
		'disable_ppage'                => '0',
		'remove_sale'                  => '0',
		'script'                       => '',
		'shop_hook'                    => 'woocommerce_before_shop_loop_item_title+15',
		'product_hook_image'           => 'woocommerce_product_thumbnails+15',
		'product_hook_label'           => 'woocommerce_product_thumbnails+15',
		'fontawesome_frontend_disable' => '',
		'fontawesome_frontend_version' => '',
	];

	public $values = [
		'settings_name' => 'br-products_label-options',
	];

	public $templates;

	/**
	 * @var BeRocket_custom_post_class
	 */
	public $custom_post;

	protected static $instance;

	public function __construct() {
		$this->custom_post = BeRocket_advanced_labels_custom_post::getInstance();

		$this->templates = [
			'css' => [
				1 => [
					'right_padding'  => '14',
					'left_padding'   => '14',
					'top_padding'    => '8',
					'bottom_padding' => '8',
					'border_radius'  => '3',
				],
				2 => [
					'image_height'  => '50',
					'image_width'   => '50',
					'line_height'   => '50',
					'border_radius' => '50%',
				],
				3 => [
					'image_height'  => '35',
					'image_width'   => '60',
					'line_height'   => '35',
					'border_radius' => '50%',
				],
				4 => [
					'image_height'  => '50',
					'image_width'   => '50',
					'line_height'   => '50',
					'border_radius' => '0',
				],
				5 => [
					'image_height'  => '35',
					'image_width'   => '60',
					'line_height'   => '35',
					'border_radius' => '0',
				],
				6 => [
					'image_height'  => '50',
					'image_width'   => '60',
					'line_height'   => '40',
					'border_radius'  => '0',
				],
				7 => [
					'image_height'  => '50',
					'image_width'   => '50',
					'line_height'   => '50',
					'border_radius'  => '0',
				],
				8 => [
					'image_height'  => '40',
					'image_width'   => '40',
					'line_height'   => '40',
					'border_radius'  => '0',
				],
				9 => [
					'image_height'  => '48',
					'image_width'   => '48',
					'line_height'   => '48',
					'border_radius'  => '50',
				],
				10 => [
					'image_height'  => '50',
					'image_width'   => '50',
					'line_height'   => '50',
					'border_radius'  => '0',
				],
				11 => [
					'image_height'  => '88',
					'image_width'   => '88',
					'line_height'   => '48',
					'border_radius'  => '0',
					'right_margin'  => '-8',
					'top_margin'    => '-8',
				],
			],
			'image' => [
				1 => [
					'image_height'  => '80',
					'image_width'   => '80',
					'line_height'   => '35',
					'border_radius' => '0',
					'right_margin'  => '0',
					'top_margin'    => '0',
				],
				2 => [
					'image_height'  => '80',
					'image_width'   => '80',
					'line_height'   => '35',
					'border_radius' => '0',
					'right_margin'  => '0',
					'top_margin'    => '0',
				],
				3 => [
					'image_height'  => '80',
					'image_width'   => '80',
					'line_height'   => '35',
					'border_radius' => '0',
					'right_margin'  => '0',
					'top_margin'    => '-9',
				],
				4 => [
					'image_height'  => '80',
					'image_width'   => '80',
					'line_height'   => '35',
					'border_radius' => '0',
					'right_margin'  => '-15',
					'top_margin'    => '-15',
				],
				5 => [
					'image_height'  => '80',
					'image_width'   => '80',
					'line_height'   => '35',
					'border_radius' => '0',
					'right_margin'  => '-15',
					'top_margin'    => '-15',
				],
				6 => [
					'image_height'  => '80',
					'image_width'   => '80',
					'line_height'   => '35',
					'border_radius' => '0',
					'right_margin'  => '-23',
					'top_margin'    => '-7',
				],
				7 => [
					'image_height'  => '100',
					'image_width'   => '80',
					'line_height'   => '35',
					'border_radius' => '0',
					'right_margin'  => '0',
					'top_margin'    => '-12',
				],
				8 => [
					'image_height'  => '120',
					'image_width'   => '80',
					'line_height'   => '35',
					'border_radius' => '0',
					'right_margin'  => '0',
					'top_margin'    => '-12',
				],
				9 => [
					'image_height'  => '80',
					'image_width'   => '100',
					'line_height'   => '35',
					'border_radius' => '0',
					'right_margin'  => '-26',
					'top_margin'    => '-26',
				],
				10 => [
					'image_height'  => '60',
					'image_width'   => '100',
					'line_height'   => '35',
					'border_radius' => '0',
					'right_margin'  => '-26',
					'top_margin'    => '6',
				],
				11 => [
					'image_height'  => '75',
					'image_width'   => '75',
					'line_height'   => '35',
					'border_radius' => '0',
					'right_margin'  => '-26',
					'top_margin'    => '6',
				],
				12 => [
					'image_height'  => '100',
					'image_width'   => '100',
					'line_height'   => '35',
					'border_radius' => '0',
					'right_margin'  => '-4',
					'top_margin'    => '-4',
				],
				13 => [
					'image_height'  => '100',
					'image_width'   => '100',
					'line_height'   => '35',
					'border_radius' => '0',
					'right_margin'  => '-4',
					'top_margin'    => '-4',
				],
				14 => [
					'image_height'  => '100',
					'image_width'   => '100',
					'line_height'   => '35',
					'border_radius' => '0',
					'right_margin'  => '-5',
					'top_margin'    => '-5',
				],
			],
			'advanced' => [],
		];

		$this->framework_data['fontawesome_frontend'] = true;

		parent::__construct($this);

		add_action('admin_init', [$this, 'admin_init']);
		add_action('admin_menu', [$this, 'admin_menu']);
		add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
		add_action('woocommerce_product_write_panel_tabs', [$this, 'product_edit_tab_link']);
		add_action('woocommerce_product_data_panels', [$this, 'product_edit_tab']);
		add_action('wp_ajax_br_label_ajax_demo', [$this, 'ajax_get_label']);
		add_action('wp_footer', [$this, 'page_load_script']);
	}

	/**
	 * Returns the URL for the plugin's directory, with a trailing slash.
	 */
	public function plugin_url(): string {
		return plugin_dir_url(__FILE__);
	}

	public function page_load_script() {
		global $berocket_display_any_advanced_labels;
		if (!empty($berocket_display_any_advanced_labels)) {
			$options = $this->get_option();
			if (!empty($options['script']['js_page_load'])) {
				echo '<script>jQuery(document).ready(function(){', $options['script']['js_page_load'], '});</script>';
			}
		}
	}

	public function remove_woocommerce_sale_flash($html): string {
		return '';
	}

	public function init() {
		parent::init();

		load_plugin_textdomain('apl_products_label_domain', false, plugin_basename(__DIR__) . '/languages');

		$options = $this->get_option();

		$shop_hook = explode('+', $options['shop_hook']);

		add_action($shop_hook[0], [$this, 'set_all_label'], intval($shop_hook[1]));

		if (!empty($options['remove_sale'])) {
			remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
			remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
			add_filter('woocommerce_sale_flash', [$this, 'remove_woocommerce_sale_flash']);
		}
		add_action('product_of_day_before_thumbnail_widget', [$this, 'set_image_label'], 20);
		add_action('product_of_day_before_title_widget', [$this, 'set_label_label_fix'], 20);
		add_action('lgv_advanced_after_img', [$this, 'set_all_label'], 20);

		if (!$options['disable_ppage']) {
			$product_hook_image = explode('+', $options['product_hook_image']);
			add_action($product_hook_image[0], [$this, 'set_image_label'], intval($product_hook_image[1]));
			if ($product_hook_image[0] == 'woocommerce_product_thumbnails') {
				add_action('woocommerce_product_thumbnails', [$this, 'move_labels_from_zoom'], 20);
			}
			$product_hook_label = explode('+', $options['product_hook_label']);
			add_action($product_hook_label[0], [$this, 'set_label_label'], intval($product_hook_label[1]));
		}

		wp_enqueue_style('advanced_product_labels_style', $this->plugin_url() . 'css/frontend.css', [], BeRocket_products_label_version);

		wp_enqueue_style('advanced_product_labels_templates_style', $this->plugin_url() . 'css/templates.css', [], BeRocket_products_label_version);

		wp_register_style('berocket_tippy', $this->plugin_url() . 'css/tippy.css', [], BeRocket_products_label_version);
		wp_register_script('berocket_tippy', $this->plugin_url() . 'js/tippy.min.js', ['jquery'], BeRocket_products_label_version, true);

		if (is_admin()) {
			wp_enqueue_style('product_labels_admin_style', $this->plugin_url() . 'css/admin.css', [], BeRocket_products_label_version);
		}
	}

	/**
	 * Function adding styles/scripts and settings to admin_init WordPress action
	 */
	public function admin_init() {
		require_once(__DIR__ . '/berocket/includes/settings_fields.php');

		register_setting($this::options_page, $this->values['settings_name'], [$this, 'save_settings_callback']);

		wp_register_script(
			'berocket_framework_admin',
			plugins_url('berocket/js/admin.js', __FILE__),
			['jquery'],
			BeRocket_products_label_version
		);

		wp_register_style(
			'berocket_framework_admin_style',
			plugins_url('berocket/css/admin.css', __FILE__),
			[],
			BeRocket_products_label_version
		);

		wp_register_style(
			'berocket_framework_global_admin_style',
			plugins_url('berocket/css/global-admin.css', __FILE__),
			[],
			BeRocket_products_label_version
		);

		wp_register_script(
			'berocket_widget-colorpicker',
			plugins_url('berocket/js/colpick.js', __FILE__),
			['jquery']
		);

		wp_register_style(
			'berocket_widget-colorpicker-style',
			plugins_url('berocket/css/colpick.css', __FILE__)
		);

		wp_register_style(
			'berocket_font_awesome',
			plugins_url('berocket/css/font-awesome.min.css', __FILE__)
		);

		wp_localize_script('berocket_framework_admin', 'berocket_framework_admin', [
			'security' => wp_create_nonce("search-products"),
		]);

		if (!empty($_GET['page']) && $_GET['page'] == $this::options_page) {
			if (function_exists('wp_enqueue_code_editor')) {
				wp_enqueue_code_editor(['type' => 'css']);
			}

			wp_enqueue_script('berocket_framework_admin');

			wp_enqueue_style('berocket_framework_admin_style');

			wp_enqueue_script('berocket_widget-colorpicker');

			wp_enqueue_style('berocket_widget-colorpicker-style');

			wp_enqueue_style('berocket_font_awesome');
		}

		wp_enqueue_style('berocket_framework_global_admin_style');

		add_filter('option_page_capability_' . $this::options_page, [$this, 'option_page_capability']);
	}

	/**
	 * Load admin file-upload scripts and styles
	 */
	public static function admin_enqueue_scripts() {
		if (function_exists('wp_enqueue_media')) {
			wp_enqueue_media();
		} else {
			wp_enqueue_style('thickbox');
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
		}
	}

	public function move_labels_from_zoom() {
		add_action('wp_footer', [$this, 'set_label_js_script']);
	}

	public function set_label_js_script() {
		?>
		<script>
			jQuery(".woocommerce-product-gallery .br_alabel").each(function(i, o) {
				jQuery(o).hide().parents(".woocommerce-product-gallery").append(jQuery(o));
			});
			galleryReadyCheck = setInterval(function() {
				if (jQuery(".woocommerce-product-gallery .woocommerce-product-gallery__trigger").length > 0 ) {
					clearTimeout(galleryReadyCheck);
					jQuery(".woocommerce-product-gallery .br_alabel").each(function(i, o) {
						jQuery(o).show().parents(".woocommerce-product-gallery").append(jQuery(o));
					});
				} else if (jQuery('.woocommerce-product-gallery__wrapper').length > 0) {
					clearTimeout(galleryReadyCheck);
					jQuery(".woocommerce-product-gallery .br_alabel").each(function(i, o) {
						jQuery(o).show().parents(".woocommerce-product-gallery").append(jQuery(o));
					});
				}
			}, 250);
		</script>
		<?php
	}

	public function set_all_label() {
		$this->set_label();
	}

	public function set_image_label() {
		$this->set_label('image');
	}

	public function set_label_label() {
		$this->set_label('label');
	}

	public function set_label_label_fix() {
		echo '<div>';
		$this->set_label('label');
		echo '<div style="clear:both;"></div></div>';
	}

	/**
	 * @global WC_Product $product
	 */
	public function set_label(string $type = '') {
		/** @var $product WC_Product */
		global $product;

		if (apply_filters('apl_prevent_all_labels', false, $type)) {
			return;
		}

		do_action('apl_set_label_start', $product);

		$product_post = br_wc_get_product_post($product);

		$options = $this->get_option();

		if (!$options['disable_plabels']) {
			$label_type = $this->custom_post->get_option($product_post->ID);
			if (!empty($label_type['label_from_post']) && is_array($label_type['label_from_post'])) {
				foreach ($label_type['label_from_post'] as $label_from_post) {
					$br_label = $this->custom_post->get_option($label_from_post);
					if (!empty($br_label)) {
						$this->show_label_on_product($br_label, $product);
					}
				}
			}
			if ((!empty($label_type['text']) && $label_type['text'] !== 'Label')
				|| (!empty($label_type['content_type']) && $label_type['content_type'] !== 'text')) {
				$this->show_label_on_product($label_type, $product);
			}
		}

		if (!$options['disable_labels']) {
			$labels = $this->getPublishedLabels();
			foreach ($labels as $label) {
				$br_label = $this->custom_post->get_option($label->ID);
				if (!$type || $type === $br_label['type']) {
					if (!isset($br_label['data']) || $this->check_label_on_post($label->ID, $br_label['data'], $product)) {
						$this->show_label_on_product($br_label, $product);
					}
				}
			}
		}

		do_action('apl_set_label_end', $product);
	}

	public function ajax_get_label() {
		if (current_user_can('manage_options')) {
			do_action('apl_set_label_start', 'demo');
			if (!empty($_POST['br_labels']['tooltip_content'])) {
				$_POST['br_labels']['tooltip_content'] = stripslashes($_POST['br_labels']['tooltip_content']);
			}
			$this->show_label_on_product($_POST['br_labels'], 'demo');
			do_action('apl_set_label_end', 'demo');
		}
		wp_die();
	}

	public function product_edit_tab_link() {
		echo '<li id="advanced-prod-label-tab"><a href="#advanced-prod-label-edit"><span>' . __('Advanced Label', 'BeRocket_tab_manager_domain') . '</span></a></li>';
	}

	public function product_edit_tab() {
		global $pagenow, $post;

		wp_enqueue_script('berocket_products_label_admin', $this->plugin_url() . 'js/admin.js', ['jquery'], BeRocket_products_label_version);
		wp_enqueue_script('berocket_framework_admin');
		wp_enqueue_style('berocket_framework_admin_style');
		wp_enqueue_script('berocket_widget-colorpicker');
		wp_enqueue_style('berocket_widget-colorpicker-style');
		wp_enqueue_style('berocket_font_awesome');
		wp_enqueue_style('product-edit-label', plugins_url('css/product-edit.css', __FILE__), [], BeRocket_products_label_version);
		set_query_var('one_product', true);

		$custom_post = BeRocket_advanced_labels_custom_post::getInstance();

		$prodOptions = [
			'label_from_post' => '',
		];

		// If we're not creating a new product now, get the possibly existing label.
		if (!strpos($pagenow, 'post-new.php')) {
			$prodOptions = $custom_post->get_option($post->ID);
		}

		$labels = $this->getPublishedLabels(); ?>
		<div class="panel wc-metaboxes-wrapper" id="advanced-prod-label-edit">
			<?php wp_nonce_field('br_labels_check', 'br_labels_nonce'); ?>
			<h4><?php _e('Labels to display on this product', 'apl_products_label_domain'); ?></h4>
			<?php
			foreach ($labels as $labelPost) {
				$checked = checked(is_array($prodOptions['label_from_post']) && in_array($labelPost->ID, $prodOptions['label_from_post']), true, false);
				echo '<p><label><input name="br_labels[label_from_post][]" type="checkbox" value="' . $labelPost->ID . '"' . $checked . '>(' . $labelPost->ID . ') ' .
						$labelPost->post_title .
					'</label></p>';
			} ?>
			<?php $custom_post->settings($post); ?>
			<div class="berocket_label_preview_wrap">
				<div class="berocket_label_preview">
					<img class="berocket_product_image" src="<?php echo plugins_url('images/labels.png', __FILE__); ?>">
				</div>
			</div>
		</div>
		<?php
	}

	public function check_label_on_post($label_id, $label_data, $product) {
		$product_id = br_wc_get_product_id($product);
		return BeRocket_conditions::check($label_data, 'berocket_advanced_label_editor', [
			'product'      => $product,
			'product_id'   => $product_id,
			'product_post' => br_wc_get_product_post($product),
		]);
	}

	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=br_labels',
			__('Product Label Settings', 'apl_products_label_domain'),
			__('Label Settings', 'apl_products_label_domain'),
			'manage_options',
			$this::options_page,
			[$this, 'option_form']
		);
	}

	public function admin_settings() {
		$tabs = [
			'General' => [
				'icon' => 'cog',
			],
			'Advanced' => [
				'icon' => 'cogs',
			],
		];

		$data = [
			'General' => [
				'disable_labels' => [
					'type'     => 'checkbox',
					'label'    => __('Disable global labels', 'apl_products_label_domain'),
					'name'     => 'disable_labels',
					'value'    => '1',
					'selected' => false,
				],
				'disable_plabels' => [
					'type'     => 'checkbox',
					'label'    => __('Disable product labels', 'apl_products_label_domain'),
					'name'     => 'disable_plabels',
					'value'    => '1',
					'selected' => false,
				],
				'disable_ppage' => [
					'type'     => 'checkbox',
					'label'    => __('Disable labels on product page', 'apl_products_label_domain'),
					'name'     => 'disable_ppage',
					'value'    => '1',
					'selected' => false,
				],
				'remove_sale' => [
					'type'     => 'checkbox',
					'label'    => __('Remove default sale label', 'apl_products_label_domain'),
					'name'     => 'remove_sale',
					'value'    => '1',
					'selected' => false,
				],
				'global_font_awesome_disable' => [
					'label'     => __('Disable Font Awesome', 'apl_products_label_domain'),
					'type'      => 'checkbox',
					'name'      => 'fontawesome_frontend_disable',
					'value'     => '1',
					'label_for' => __('Disable loading CSS file for Font Awesome icons; recommended only if you do not use Font Awesome icons in widgets or you have Font Awesome in your theme.', 'apl_products_label_domain'),
				],
				'global_fontawesome_version' => [
					'label'    => __('Font Awesome Version', 'apl_products_label_domain'),
					'name'     => 'fontawesome_frontend_version',
					'type'     => 'selectbox',
					'options'  => [
						['value' => '', 'text' => __('Font Awesome 4', 'apl_products_label_domain')],
						['value' => 'fontawesome5', 'text' => __('Font Awesome 5', 'apl_products_label_domain')],
					],
					'value'    => '',
					'label_for' => __('Version of Font Awesome that will be used. Please select a version that you have in your theme', 'apl_products_label_domain'),
				],
			],
			'Advanced' => [
				'shop_hook' => [
					'type'     => 'selectbox',
					'options'  => [
						['value' => 'woocommerce_before_shop_loop_item_title+15',  'text' => __('Before Title 1', 'apl_products_label_domain')],
						['value' => 'woocommerce_shop_loop_item_title+5',          'text' => __('Before Title 2', 'apl_products_label_domain')],
						['value' => 'woocommerce_after_shop_loop_item_title+5',    'text' => __('After Title', 'apl_products_label_domain')],
						['value' => 'woocommerce_before_shop_loop_item+5',         'text' => __('Before All', 'apl_products_label_domain')],
						['value' => 'woocommerce_after_shop_loop_item+500',        'text' => __('After All', 'apl_products_label_domain')],
						['value' => 'berocket_disabled_label_hook_shop+10',        'text' => __('{DISABLED}', 'apl_products_label_domain')],
					],
					'label'     => __('Shop Hook', 'apl_products_label_domain'),
					'label_for' => __('Where labels will be displayed on shop page. In different theme it can be different place. (This means that it is supposed to be in this place)', 'apl_products_label_domain'),
					'name'      => 'shop_hook',
					'value'     => $this->defaults['shop_hook'],
				],
				'product_hook_image' => [
					'type'     => 'selectbox',
					'options'  => [
						['value' => 'woocommerce_product_thumbnails+15',               'text' => __('Under thumbnails', 'apl_products_label_domain')],
						['value' => 'woocommerce_before_single_product_summary+50',    'text' => __('After Images', 'apl_products_label_domain')],
						['value' => 'woocommerce_single_product_summary+2',            'text' => __('Before Summary Data', 'apl_products_label_domain')],
						['value' => 'woocommerce_single_product_summary+100',          'text' => __('After Summary Data', 'apl_products_label_domain')],
						['value' => 'woocommerce_before_single_product_summary+5',     'text' => __('Before All', 'apl_products_label_domain')],
						['value' => 'berocket_disabled_label_hook_image+10',           'text' => __('{DISABLED}', 'apl_products_label_domain')],
					],
					'label'     => __('Product Hook Image', 'apl_products_label_domain'),
					'label_for' => __('Where on image labels will be displayed on product page. In different theme it can be different place(This means that it is supposed to be in this place)', 'apl_products_label_domain'),
					'name'      => 'product_hook_image',
					'value'     => $this->defaults['product_hook_image'],
				],
				'product_hook_label' => [
					'type'     => 'selectbox',
					'options'  => [
						['value' => 'woocommerce_product_thumbnails+10',               'text' => __('Under thumbnails', 'apl_products_label_domain')],
						['value' => 'woocommerce_before_single_product_summary+50',    'text' => __('After Images', 'apl_products_label_domain')],
						['value' => 'woocommerce_single_product_summary+2',            'text' => __('Before Summary Data', 'apl_products_label_domain')],
						['value' => 'woocommerce_single_product_summary+7',            'text' => __('After Title', 'apl_products_label_domain')],
						['value' => 'woocommerce_single_product_summary+100',          'text' => __('After Summary Data', 'apl_products_label_domain')],
						['value' => 'woocommerce_before_single_product_summary+5',     'text' => __('Before All', 'apl_products_label_domain')],
						['value' => 'berocket_disabled_label_hook_labels+10',          'text' => __('{DISABLED}', 'apl_products_label_domain')],
					],
					'label'     => __('Product Hook Label', 'apl_products_label_domain'),
					'label_for' => __('Where default labels will be displayed on product page. In different theme it can be different place(This means that it is supposed to be in this place)', 'apl_products_label_domain'),
					'name'      => 'product_hook_label',
					'value'     => $this->defaults['product_hook_label'],
				],
			],
		];

		$setup_style = [
			'settings_url' => admin_url('admin.php?page=' . $this::options_page),
		];

		$this->display_admin_settings($tabs, $data, $setup_style);
	}

	/**
	 * Function add options form to settings page
	 */
	public function option_form() {
		?>
		<div class="wrap br_framework_settings">
			<div id="icon-themes" class="icon32"></div>
			<h1><?php _e('Settings for Advanced Product Labels', 'BeRocket_domain'); ?></h1>
			<h4 class="sub-head">Customize labels placed on products.</h4>
			<a href="https://github.com/dchenk/advanced-product-labels-for-woocommerce" title="Plugin Support" target="_blank">Support</a>
			<?php settings_errors(); ?>
			<?php $this->admin_settings(); ?>
		</div>
		<?php
	}

	/**
	 * @return WP_Post[]
	 */
	private function getPublishedLabels(): array {
		$args = [
			'posts_per_page'   => -1,
			'offset'           => 0,
			'orderby'          => 'none',
			'post_type'        => 'br_labels',
			'post_parent'      => '',
			'post_status'      => 'publish',
			'suppress_filters' => false,
		];
		return get_posts($args);
	}

	/**
	 * @param $br_label array
	 * @param $product WC_Product|string
	 */
	private function show_label_on_product($br_label, $product) {
		global $berocket_display_any_advanced_labels;

		$berocket_display_any_advanced_labels = true;

		if (empty($br_label) || !is_array($br_label)) {
			return;
		}

		// Make sure the content_type property exists.
		if (empty($br_label['content_type'])) {
			$br_label['content_type'] = 'text';
		}

		if ($product === 'demo') {
			$br_label['text'] = stripslashes($br_label['text']);
		}

		if ($br_label['color'][0] != '#') {
			$br_label['color'] = '#' . $br_label['color'];
		}

		if (isset($br_label['font_color']) && $br_label['font_color'][0] != '#') {
			$br_label['font_color'] = '#' . $br_label['font_color'];
		}

		switch ($br_label['content_type']) {
		case 'sale_p':
			$br_label['text'] = '';
			if ($product === 'demo' || $product->is_on_sale()) {
				$price_ratio = false;
				if ($product === 'demo') {
					$product_sale = '250.5';
					$product_regular = '430.25';
					$price_ratio = $product_sale / $product_regular;
				} else {
					/** @var WC_Product $product */
					$product_sale = br_wc_get_product_attr($product, 'sale_price');
					$product_regular = br_wc_get_product_attr($product, 'regular_price');
					if (!empty($product_sale) && $product_sale != $product_regular) {
						$price_ratio = $product_sale / $product_regular;
					}
					if ($product->has_child()) {
						foreach ($product->get_children() as $child_id) {
							$child = br_wc_get_product_attr($product, 'child', $child_id);
							$child_sale = br_wc_get_product_attr($child, 'sale_price');
							$child_regular = br_wc_get_product_attr($child, 'regular_price');
							if (!empty($child_sale) && $child_sale != $child_regular) {
								$price_ratio2 = $child_sale / $child_regular;
								if ($price_ratio === false || $price_ratio2 < $price_ratio) {
									$price_ratio = $price_ratio2;
								}
							}
						}
					}
				}
				if ($price_ratio !== false) {
					$price_ratio = $price_ratio * 100;
					$price_ratio = number_format($price_ratio, 0, '', '');
					$price_ratio = $price_ratio * 1;
					$br_label['text'] = (100 - $price_ratio) . "%";
					if (!empty($br_label['discount_minus'])) {
						$br_label['text'] = '-' . $br_label['text'];
					}
				}
			}
			if (empty($br_label['text'])) {
				$br_label['text'] = false;
			}
			break;
		case 'price':
			$br_label['text'] = '';
			if ($product === 'demo') {
				$price = '250.5';
				$br_label['text'] = wc_price($price);
			} else {
				if ($product->is_type('variable') || $product->is_type('grouped')) {
					$br_label['text'] = $product->get_price_html();
				} else {
					$price = br_wc_get_product_attr($product, 'price');
					$br_label['text'] = wc_price($price);
				}
			}
			break;
		case 'stock_status':
			$br_label['text'] = '';
			if ($product === 'demo') {
				$br_label['text'] = sprintf(__('%s in stock', 'woocommerce'), 24);
			} else {
				$br_label['text'] = $product->get_availability()['availability'];
			}
		}

		$label_style = '';
		if (!empty($br_label['image_height'])) {
			$label_style .= 'height: ' . $br_label['image_height'] . 'px;';
		}
		if (!empty($br_label['image_width'])) {
			$label_style .= 'width: ' . $br_label['image_width'] . 'px;';
		}
		if (empty($br_label['image_height']) && empty($br_label['image_width'])) {
			$label_style .= 'padding: 0.2em 0.5em;';
		}

		if (!empty($br_label['color']) && !empty($br_label['color_use'])) {
			$label_style .= 'background-color:' . $br_label['color'] . ';';
		}

		if (!empty($br_label['font_color'])) {
			$label_style .= 'color:' . $br_label['font_color'] . ';';
		}
		if (isset($br_label['border_radius'])) {
			if (strpos($br_label['border_radius'], 'px') === false
				&& strpos($br_label['border_radius'], 'em') === false
				&& strpos($br_label['border_radius'], '%') === false) {
				$br_label['border_radius'] .= 'px';
			}
			$label_style .= 'border-radius:' . $br_label['border_radius'] . ';';
		}
		if (isset($br_label['line_height'])) {
			$label_style .= 'line-height:' . $br_label['line_height'] . 'px;';
		}

		$div_style = '';
		if (isset($br_label['padding_top'])) {
			$div_style .= 'top:' . $br_label['padding_top'] . 'px;';
		}
		if (isset($br_label['padding_horizontal']) && $br_label['position'] != 'center') {
			$div_style .= ($br_label['position'] == 'left' ? 'left:' : 'right:') . $br_label['padding_horizontal'] . 'px;';
		}

		// $div_classes is the classes set on the outer div element.
		$div_classes = [
			'br_alabel',
			'br_alabel_' . $br_label['type'],
			'br_label_type_' . $br_label['content_type'],
			'br_alabel_' . $br_label['position'],
		];

		$br_label['text'] = apply_filters('advanced_product_labels_label_text', $br_label['text'] ?? '', $br_label, $product);
		$div_style = apply_filters('advanced_product_labels_div_style', $div_style, $br_label, $product);
		$div_classes = apply_filters('advanced_product_labels_div_class', $div_classes, $br_label, $product);
		$label_style = apply_filters('advanced_product_labels_label_style', $label_style, $br_label, $product);

		if ($br_label['content_type'] == 'text' && empty($br_label['text'])) {
			return;
		}

		if (!is_array($br_label['text'])) {
			$br_label['text'] = [$br_label['text']];
		}

		if (in_array($br_label['content_type'], apply_filters('apl_content_type_with_before_after', ['sale_p']), true)) {
			foreach ($br_label['text'] as &$br_label_text) {
				$br_label_text = (empty($br_label['text_before']) ? '' : $br_label['text_before'] . (empty($br_label['text_before_nl']) ? '' : '<br>'))
					. $br_label_text
					. (empty($br_label['text_after']) ? '' : (empty($br_label['text_after_nl']) ? '' : '<br>') . $br_label['text_after']);
			}
		}

		foreach ($br_label['text'] as $text) {
			if (! empty($text) && $text[0] == '#') {
				$label_style = $label_style . ' background-color:' . $text . ';';
				$text = '';
			}
			$tooltip_data = '';
			if (! empty($br_label['tooltip_content'])) {
				$br_label['tooltip_open_delay'] = (empty($br_label['tooltip_open_delay']) ? '0' : $br_label['tooltip_open_delay']);
				$br_label['tooltip_close_delay'] = (empty($br_label['tooltip_close_delay']) ? '0' : $br_label['tooltip_close_delay']);
				$tooltip_data .= ' data-tippy-delay="[' . $br_label['tooltip_open_delay'] . ', ' . $br_label['tooltip_close_delay'] . ']"';
				if (!empty($br_label['tooltip_position'])) {
					$tooltip_data .= ' data-tippy-placement="' . $br_label['tooltip_position'] . '"';
				}
				if (!empty($br_label['tooltip_max_width'])) {
					$tooltip_data .= ' data-tippy-maxWidth="' . $br_label['tooltip_max_width'] . 'px"';
				}
				if (!empty($br_label['tooltip_open_on'])) {
					$tooltip_data .= ' data-tippy-trigger="' . $br_label['tooltip_open_on'] . '"';
				}
				if (!empty($br_label['tooltip_theme'])) {
					$tooltip_data .= ' data-tippy-theme="' . $br_label['tooltip_theme'] . '"';
				}
				$tooltip_data .= ' data-tippy-hideOnClick="' . (empty($br_label['tooltip_close_on_click']) ? 'false' : 'true') . '"';
				$tooltip_data .= ' data-tippy-arrow="' . (empty($br_label['tooltip_use_arrow']) ? 'false' : 'true') . '"';
			}

			$custom_styling = [
				'div_custom_class',
				'div_custom_css',
				'span_custom_class',
				'b_custom_class',
				'i1_custom_class',
				'i2_custom_class',
				'i3_custom_class',
				'i4_custom_class',
			];

			// Make sure that each possible attribute value is a string.
			foreach ($custom_styling as $cs) {
				if (empty($br_label[$cs])) {
					$br_label[$cs] = '';
				}
			}

			if ($br_label['div_custom_class']) {
				array_push($div_classes, $br_label['div_custom_class']);
			}

			$label_style .= $br_label['span_custom_css'];

			$html = '<div class="' . esc_attr(implode(' ', $div_classes)) . '" style="' . esc_attr($div_style) . '">';
			$html .= '<span' . $tooltip_data . ' style="' . esc_attr($label_style) . '"' . (empty($br_label['span_custom_class']) ? '' : ' class="' . esc_attr($br_label['div_custom_class']) . '"') . '>';
			$html .= '<i class="template-span-before ' . $br_label['i1_custom_class'] . '"></i>';
			$html .= '<i class="template-i ' . $br_label['i2_custom_class'] . '"></i>';
			$html .= '<i class="template-i-before ' . $br_label['i3_custom_class'] . '"></i>';
			$html .= '<i class="template-i-after ' . $br_label['i4_custom_class'] . '"></i>';
			$html .= '<b' . (empty($br_label['b_custom_class']) ? '' : ' class="' . esc_attr($br_label['b_custom_class']) . '"') . '>' . $text . '</b>';

			if (!empty($br_label['tooltip_content'])) {
				$html .= '<div style="display: none;" class="br_tooltip">' . $br_label['tooltip_content'] . '</div>';
				wp_enqueue_style('berocket_tippy');
				wp_enqueue_script('berocket_tippy');
			}

			$html .= '</span>';
			$html .= '</div>';
			$html = apply_filters('apl_show_label_on_product_html', $html, $br_label, $product);
			echo $html;
		}
	}
}

new BeRocket_products_label;
