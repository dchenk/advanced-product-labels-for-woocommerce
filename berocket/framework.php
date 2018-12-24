<?php

/*$BeRocket_framework_this_version = 1;
$BeRocket_framework_latest_version_exist = get_option('BeRocket_framework_latest_version_exist');
if( empty($BeRocket_framework_latest_version_exist) || empty($BeRocket_framework_latest_version_exist['version']) || $BeRocket_framework_latest_version_exist['version'] < $BeRocket_framework_this_version ) {
	$BeRocket_framework_latest_version_exist = array('version' => $BeRocket_framework_this_version, 'file' => __FILE__);
	update_option('BeRocket_framework_latest_version_exist', $BeRocket_framework_latest_version_exist);
} elseif( file_exists($BeRocket_framework_latest_version_exist['file']) && $BeRocket_framework_latest_version_exist['file'] != __FILE__ ) {
	require_once($BeRocket_framework_latest_version_exist['file']);
}*/
require_once(__DIR__ . '/includes/functions.php');
require_once(__DIR__ . '/includes/admin_notices.php');
require_once(__DIR__ . '/includes/custom_post.php');
require_once(__DIR__ . '/includes/conditions.php');
require_once(__DIR__ . '/includes/plugin-variation.php');
include_once(ABSPATH . 'wp-admin/includes/plugin.php');

load_plugin_textdomain('BeRocket_domain', false, plugin_basename(__DIR__) . '/languages');

foreach (glob(__DIR__ . '/' . "../includes/*.php") as $filename) {
	include_once($filename);
}

if (!class_exists('BeRocket_Framework')) {
	class BeRocket_Framework {
		public static $settings_name = '';
		protected static $instance;
		protected $plugin_version_capability = 0;
		protected $framework_data = [
			'fontawesome_frontend' => false,
		];
		protected $global_settings = [
			'fontawesome_frontend_disable',
			'fontawesome_frontend_version',
		];
		private $post;

		/**
		 * @var BeRocket_products_label The child class object
		 */
		private $cc;

		public function __construct($child) {
			if (null === static::$instance) {
				static::$instance = $child;
			}

			$this->cc = $child;

			do_action('BeRocket_framework_init_plugin', $this->cc->info);
			$this->plugin_version_capability = apply_filters('brfr_plugin_version_capability_' . $this->cc->info['plugin_name'], $this->plugin_version_capability, $this);
			$this->defaults = apply_filters('brfr_plugin_defaults_value_' . $this->cc->info['plugin_name'], $this->defaults, $this);

			register_activation_hook($this->cc->info['plugin_file'], [$this, 'activation']);
			register_uninstall_hook($this->cc->info['plugin_file'], [get_class($this->cc), 'deactivation']);

			add_action('init', [$this->cc, 'init']);
			add_action('admin_enqueue_scripts', [$this->cc, 'admin_enqueue_scripts']);

			add_action('wp_ajax_br_' . $this->cc->info['plugin_name'] . '_settings_save', [
				$this->cc,
				'save_settings',
			]);

			add_filter('plugin_row_meta', [$this->cc, 'plugin_row_meta'], 10, 2);
			add_filter('is_berocket_settings_page', [$this->cc, 'is_settings_page']);

			$plugin_base_slug = plugin_basename($this->cc->info['plugin_file']);
			add_filter('plugin_action_links_' . $plugin_base_slug, [$this->cc, 'plugin_action_links']);

			add_action('plugins_loaded', [$this->cc, 'plugins_loaded']);
			add_action('sanitize_comment_cookies', [$this->cc, 'sanitize_comment_cookies']);
			add_action('install_plugins_pre_plugin-information', [$this->cc, 'install_plugins_pre_plugin_information'], 1);

			if (empty($this->plugin_version_capability) || $this->plugin_version_capability < 10) {
				add_filter('berocket_admin_notices_subscribe_plugins', [$this, 'admin_notices_subscribe_plugins']);
			}

			do_action($this->info['plugin_name'] . '_framework_construct', $this->cc);
		}

		public static function getInstance(): BeRocket_Framework {
			if (null === static::$instance) {
				static::$instance = new static(null);
			}
			return static::$instance;
		}

		public function admin_notices_subscribe_plugins($plugins) {
			$plugins[] = $this->info['id'];
			return $plugins;
		}

		public function install_plugins_pre_plugin_information() {
			wp_print_styles('font-awesome');
		}

		public function plugins_loaded() {
			if (!empty($_POST[$this->cc->values['settings_name']])) {
				$this->post = berocket_sanitize_array($_POST[$this->cc->values['settings_name']]);
			}
		}

		public function sanitize_comment_cookies() {
			if (!empty($this->post)) {
				$_POST[$this->cc->values['settings_name']] = $this->post;
			}
		}

		/**
		 * Function set default settings to database
		 */
		public function activation() {
			add_option($this->cc->values['settings_name'], self::sanitize_option($this->cc->defaults));
		}

		/**
		 * Function remove settings from database
		 */
		public static function deactivation() {
			if (!empty(static::$settings_name)) {
				do_action('brfr_deactivate_' . static::$settings_name);
				delete_option(static::$settings_name);
			}
		}

		/**
		 * Action links on the Plugins page
		 */
		public function plugin_action_links($links) {
			$action_links = [
				'settings' => '<a href="' . admin_url('admin.php?page=' . $this->cc->values['option_page']) .
							  '" title="' . __('View Plugin Settings', 'BeRocket_domain') . '">' .
							  __('Settings', 'BeRocket_domain') . '</a>',
			];

			return apply_filters('brfr_action_link_' . $this->cc->info['plugin_name'], array_merge($action_links, $links));
		}

		/**
		 * Meta links on the Plugins page
		 */
		public function plugin_row_meta($links, $file) {
			$plugin_base_slug = plugin_basename($this->cc->info['plugin_file']);
			if ($file == $plugin_base_slug) {
				$row_meta = [
					'docs' => '<a href="https://berocket.com/docs/plugin/' .
						$this->cc->values['premium_slug'] . '" title="' .
						__('View Plugin Documentation', 'BeRocket_domain') .
						'" target="_blank">' . __('Docs', 'BeRocket_domain') . '</a>',
				];

				if (!empty($this->plugin_version_capability) && $this->plugin_version_capability > 10) {
					$row_meta['premium'] = '<a href="https://berocket.com/support/product/' . $this->cc->values['premium_slug'] .
						'" title="' . __('View Premium Support Page', 'BeRocket_domain') .
						'" target="_blank">' . __('Premium Support', 'BeRocket_domain') . '</a>';
				} else {
					$row_meta['premium'] = '<a href="https://berocket.com/product/' . $this->cc->values['premium_slug'] .
						'" title="' . __('View Premium Version Page', 'BeRocket_domain') .
						'" target="_blank">' . __('Premium Version', 'BeRocket_domain') . '</a>';
				}

				$links = array_merge($links, $row_meta);
				$links = apply_filters('brfr_plugin_row_meta_' . $this->cc->info['plugin_name'], $links, $file, $this);
			}

			return $links;
		}

		/**
		 * Initialize
		 */
		public function init() {
			wp_enqueue_script('jquery');
			wp_register_style('font-awesome', plugins_url('berocket/css/font-awesome.min.css', $this->cc->info['plugin_file']));
			wp_register_style('font-awesome-5', plugins_url('berocket/css/fontawesome5.min.css', $this->cc->info['plugin_file']));
			wp_register_style('font-awesome-5-compat', plugins_url('berocket/css/fontawesome4-compat.min.css', $this->cc->info['plugin_file']));

			if (is_admin()) {
				wp_enqueue_style('font-awesome');
			} elseif (!empty($this->framework_data['fontawesome_frontend'])) {
				$this->enqueue_fontawesome();
			}

			wp_add_inline_script(
				$this->cc->info['plugin_name'] . "_execute_func",
				";
                (function($) {
                    $(document).ready(function() {
                        " . $this->cc->info['plugin_name'] . "_execute_func( the_" . $this->cc->info['plugin_name'] . "_js_data.script.js_page_load );
                    });
                })(jQuery);

                function " . $this->cc->info['plugin_name'] . "_execute_func ( func ) {
                    if (the_" . $this->cc->info['plugin_name'] . "_js_data.script != 'undefined'
                        && the_" . $this->cc->info['plugin_name'] . "_js_data.script != null
                        && typeof func != 'undefined'
                        && func.length > 0 ) {
                        try {
                            eval( func );
                        } catch(err) {
                        alert('You have some incorrect JavaScript code (" . $this->cc->info['norm_name'] . ")');
                    }
                }"
			);
		}

		public function enqueue_fontawesome($force = false) {
			if (!wp_style_is('font-awesome-5-compat', 'registered')) {
				wp_register_style('font-awesome', plugins_url('berocket/css/font-awesome.min.css', $this->cc->info['plugin_file']));
				wp_register_style('font-awesome-5', plugins_url('berocket/css/fontawesome5.min.css', $this->cc->info['plugin_file']));
				wp_register_style('font-awesome-5-compat', plugins_url('berocket/css/fontawesome4-compat.min.css', $this->cc->info['plugin_file']));
			}
			$global_option = $this->get_global_option();
			if (empty($global_option['fontawesome_frontend_disable'])) {
				if (br_get_value_from_array($global_option, 'fontawesome_frontend_version') == 'fontawesome5') {
					wp_enqueue_style('font-awesome-5');
				} else {
					wp_enqueue_style('font-awesome');
				}
			} else {
				if (br_get_value_from_array($global_option, 'fontawesome_frontend_version') == 'fontawesome5') {
					wp_enqueue_style('font-awesome-5-compat');
				}
			}
		}

		public function option_page_capability($capability) {
			return 'manage_woocommerce';
		}

		/**
		 * Function add options form to settings page
		 */
		public function option_form() {
			?>
			<div class="wrap br_framework_settings br_<?php echo $this->cc->info['plugin_name']; ?>_settings">
				<div id="icon-themes" class="icon32"></div>
				<h2><?php echo $this->cc->info['full_name'] . ' ' . __('Settings', 'BeRocket_domain'); ?></h2>
				<?php settings_errors(); ?>
				<?php $this->cc->admin_settings(); ?>
			</div>
			<?php
		}

		public function admin_settings($tabs_info = [], $data = []) {
			$setup_style = func_get_args();
			$setup_style = empty($setup_style[2]) || !is_array($setup_style[2]) ? [] : $setup_style[2];
			$setup_style['settings_url'] = admin_url('admin.php?page=' . $this->cc->values['option_page']);
			$this->display_admin_settings($tabs_info, $data, $setup_style);
		}

		/**
		 * Function generate settings page
		 *
		 * @var $data - array with settings data, page will be build using this
		 */
		public function display_admin_settings($tabs_info = [], $data = [], $setup_style = []) {
			$def_setup_style = [
				'settings_url' => add_query_arg(null, null),
				'use_filters_hook' => true,
				'hide_header' => false,
				'hide_header_links' => false,
				'hide_save_button' => false,
				'hide_form' => false,
				'hide_additional_blocks' => false,
				'header_name' => $this->cc->info['norm_name'],
				'header_description' => 'Customize labels placed on products',
				'settings_name' => $this->cc->values['settings_name'],
				'options' => $this->get_option(),
				'name_for_filters' => $this->cc->info['plugin_name'],
			];

			$setup_style = array_merge($def_setup_style, $setup_style);
			if ($setup_style['use_filters_hook']) {
				$tabs_info = apply_filters('brfr_tabs_info_' . $setup_style['name_for_filters'], $tabs_info);
				$data = apply_filters('brfr_data_' . $setup_style['name_for_filters'], $data);
			}
			if (isset($data) && is_array($data) && count($data)) {
				$page_menu = '';
				$is_first  = true;
				$title     = '';
				$options   = $setup_style['options'];

				$selected_tab = false;
				if (!empty($_GET['tab'])) {
					foreach ($tabs_info as $tab_name => $tab_info) {
						if (sanitize_title($tab_name) == $_GET['tab']) {
							$selected_tab = true;
						}
					}
				}

				foreach ($tabs_info as $tab_name => $tab_info) {
					$page_menu .= '<li>';

					$page_menu .= '<a href="' . (empty($tab_info['link']) ? add_query_arg('tab', sanitize_title($tab_name), $setup_style['settings_url']) : $tab_info['link']) .
						'" class="' . (empty($tab_info['priority']) ? 'default' : $tab_info['priority']) .
						(empty($tab_info['link']) ? '' : ' redirect_link') .
						($selected_tab ? (sanitize_title($tab_name) == $_GET['tab'] ? ' active' : '') : ($is_first ? ' active' : '')) .
						'" data-block="' . (empty($tab_info['link']) ? 'berocket_framework_menu_' . sanitize_title($tab_name) : 'redirect_link') . '">';

					if ($tab_info['icon']) {
						$page_menu .= '<span class="fa fa-' . $tab_info['icon'] . '"></span>';
					}
					$page_menu .= $tab_name . '</a>';

					$page_menu .= '</li>';

					if ($is_first) {
						if ($tab_info['icon']) {
							$title .= '<span class="fa fa-' . $tab_info['icon'] . '"></span>';
						}
						$title .= $tab_name;
					}

					$is_first = false;
				}

				if ($setup_style['use_filters_hook']) {
					$page_menu = apply_filters('brfr_page_menu_' . $setup_style['name_for_filters'], $page_menu, $tabs_info);
				}

				$is_first     = true;
				$page_content = '';

				foreach ($data as $tab_name => $tab_content) {
					$page_content .= '<div class="nav-block berocket_framework_menu_' . sanitize_title($tab_name) . '-block ' . ($selected_tab ? (sanitize_title($tab_name) == $_GET['tab'] ? ' nav-block-active' : '') : ($is_first ? ' nav-block-active' : '')) . '">';
					$page_content .= '<table class="framework-form-table berocket_framework_menu_' . sanitize_title($tab_name) . '">';

					if (isset($tab_content) && is_array($tab_content) && count($tab_content)) {
						foreach ($tab_content as $item) {
							$class = $extra = '';

							if (isset($item['class']) && trim($item['class'])) {
								$class = " class='" . trim($item['class']) . "'";
							}

							if (isset($item['extra']) && trim($item['extra'])) {
								$extra = " " . trim($item['extra']);
							}

							$item['tr_class'] = (empty($item['tr_class']) ? '' : ' class="' . $item['tr_class'] . '"');
							$page_content .= "<tr" . $item['tr_class'] . ">";

							if (empty($item['section']) || $item['section'] == 'field') {
								$item['td_class'] = (empty($item['td_class']) ? '' : ' class="' . $item['td_class'] . '"');
								$page_content .= '<th scope="row">' . $item['label'] . '</th><td' . $item['td_class'] . '>';

								$field_items = [];
								if (isset($item['items']) && is_array($item['items'])) {
									$field_items = $item['items'];
								} else {
									$field_items[] = $item;
								}
								$item_i = 0;
								foreach ($field_items as $item_key => $field_item) {
									$class = $extra = $item_content = '';
									if (isset($field_item['class']) && trim($field_item['class'])) {
										$class = " class='" . trim($field_item['class']) . "'";
									}

									if (isset($field_item['extra']) && trim($field_item['extra'])) {
										$extra = " " . trim($field_item['extra']);
									}
									if (isset($option_values)) {
										unset($option_values);
									}
									if (empty($field_item['custom_name']) && ! empty($field_item['name'])) {
										if (is_array($field_item['name'])) {
											$option_values = $options;
											$option_deault_values = $this->cc->defaults;
											foreach ($field_item['name'] as $field_names) {
												if (isset($option_values, $field_names)   && $field_names !== '' && isset($option_values[$field_names])) {
													$option_values = $option_values[$field_names];
												} else {
													unset($option_values);
												}
												if (isset($option_values, $field_names)   && $field_names !== '' && isset($option_deault_values[$field_names])) {
													$option_deault_values = $option_deault_values[$field_names];
												} else {
													unset($option_deault_values);
												}
											}
										} elseif (isset($options[$field_item['name']])) {
											$option_values = $options[$field_item['name']];
											if (isset($this->cc->defaults[$field_item['name']])) {
												$option_deault_values = $this->cc->defaults[$field_item['name']];
											}
										}
									}
									if (! isset($option_values)) {
										$option_values = null;
									}
									if (! isset($option_deault_values)) {
										$option_deault_values = null;
									}
									if ($field_item['type'] != 'checkbox' && isset($option_values)) {
										$value = $option_values;
									} else {
										$value = $field_item['value'];
									}
									if ($item_i > 0) {
										$page_content .= '<span class="br_line_delimiter"></span> ';
									}
									$field_item['label_for'] = ($field_item['label_for'] ?? '');
									$field_item['label_be_for'] = ($field_item['label_be_for'] ?? '');

									$field_name = (
										empty($field_item['custom_name']) ?
										(
											empty($field_item['name']) ?
											$setup_style['settings_name'] . '[]' :
											(
												is_array($field_item['name']) ?
												$setup_style['settings_name'] . '[' . implode('][', $field_item['name']) . ']' :
												$setup_style['settings_name'] . '[' . $field_item['name'] . ']'
											)
										) :
										$field_item['custom_name']
									);
									if (empty($field_item['type'])) {
										$field_item['type'] = 'text';
									}
									$item_filtered_content = apply_filters('berocket_framework_item_content_' . $field_item['type'], '', $field_item, $field_name, $value, $class, $extra, $option_values, $option_deault_values);
									if (empty($item_filtered_content)) {
										$item_content .= "<td class='error'>Unsupported field type!</td>";
									} else {
										$item_content .= $item_filtered_content;
									}
									if ($setup_style['use_filters_hook']) {
										$item_content = apply_filters('brfr_fields_html_' . $setup_style['name_for_filters'], $item_content, $field_name, $value, $field_item);
									}
									$page_content .= $item_content;
									$item_i++;
								}

								$page_content .= '</td>';
							} elseif ($item['section'] == 'header') {
								$page_content .= "
                                <th colspan='2'>
                                    <h{$item['type']}" . $class . $extra . ">{$item['label']}</h{$item['type']}>
                                </th>";
							} elseif (method_exists($this->cc, 'section_' . $item['section'])) {
								$section_filter = $this->cc->{'section_' . $item['section']}($item, $options);
								if ($setup_style['use_filters_hook']) {
									$section_filter = apply_filters('brfr_' . $setup_style['name_for_filters'] . '_' . $item['section'], $section_filter, $item, $options, $setup_style['settings_name']);
								}
								$page_content .= $section_filter;
							} else {
								$section_filter = apply_filters('brfr_' . $setup_style['name_for_filters'] . '_' . $item['section'], '', $item, $options, $setup_style['settings_name']);
								if (! empty($section_filter)) {
									$page_content .= $section_filter;
								} else {
									$page_content .= "<th colspan='2' class='error'>Not supported section type `{$item['section']}`</th>";
								}
							}

							$page_content .= "</tr>";
						}
					}

					$page_content .= '</table>';
					$page_content .= '</div>';

					$is_first = false;
				}

				if ($setup_style['use_filters_hook']) {
					$page_content = apply_filters('brfr_page_content_' . $setup_style['name_for_filters'], $page_content, $data);
				}

				if (!$setup_style['hide_header']) {
					echo "<header>
							<nav class='premium'>";
					if (!$setup_style['hide_header_links']) {
						$header_links = [];
						$header_links['support'] = [
							'text' => '<i class="fa fa-support"></i>',
							'link' => 'https://github.com/dchenk/advanced-product-labels-for-woocommerce',
						];
						$header_links = apply_filters('brfr_header_links_' . $setup_style['name_for_filters'], $header_links);
						foreach ($header_links as $header_link_title => $header_link) {
							echo '<a href="' . $header_link['link'] . '" title="' . $header_link_title . '" target="_blank">' . $header_link['text'] . '</a>';
						}
					}
					echo "</nav>
							<div class='br_plugin_name'>
								<h1>{$setup_style['header_name']}</h1>
								<h3>{$setup_style['header_description']}</h3>
							</div>
						</header>";
				}
				echo '<div class="body">';
				echo '<ul class="side">';
				echo $page_menu;
				echo '</ul>';
				echo '<div class="content">';
				echo "<div class='title'>{$title}</div>";
				if (! $setup_style['hide_form']) {
					echo '<form data-plugin="' . $this->cc->info['plugin_name'] . '" class="br_framework_submit_form ' . $this->cc->info['plugin_name'] . '_submit_form ' . ((isset($this->plugin_version_capability) && $this->plugin_version_capability <= 5) ? 'show_premium' : '') .
							 '" method="post" action="options.php">';
					settings_fields($_GET['page']);
				}
				echo $page_content;
				echo '<div class="clear-both"></div>';
				if (! $setup_style['hide_save_button']) {
					echo '<input type="submit" class="button-primary button" value="' . __('Save Changes', 'BeRocket_domain') . '" />';
					echo '<div class="br_save_error"></div>';
				}
				if (! $setup_style['hide_form']) {
					echo '</form>';
				}
				echo '</div>';
				echo '<div class="clear-both"></div>';
				echo '</div>';
			}
		}

		/**
		 * Load template from theme | plugin
		 *
		 * @access public
		 *
		 * @param string $name template name
		 *
		 * @return void
		 */
		public function br_get_template_part($name = '') {
			$template = '';

			// Look in your_child_theme/woocommerce-%PLUGINNAME%/name.php
			if ($name) {
				$template = locate_template("woocommerce-" . $this->cc->info['plugin_name'] . "/{$name}.php");
			}

			// Get default slug-name.php
			if (! $template && $name && file_exists($this->cc->info['templates'] . "{$name}.php")) {
				$template = $this->cc->info['templates'] . "{$name}.php";
			}

			// Allow 3rd party plugin filter template file from their plugin
			$template = apply_filters($this->cc->info['plugin_name'] . '_get_template_part', $template, $name);

			if ($template) {
				load_template($template, false);
			}
		}

		/**
		 * Load admin file-upload scripts and styles
		 *
		 * @access public
		 *
		 * @return void
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
		public function save_settings_callback($settings) {
			if (isset($settings)) {
				$settings = self::sanitize_option($settings);
				if (count($this->global_settings)) {
					$global_options = $this->get_global_option();
					foreach ($this->global_settings as $global_setting) {
						if (isset($settings[$global_setting])) {
							$global_options[$global_setting] = $settings[$global_setting];
						}
					}
					$this->save_global_option($global_options);
				}
			}
			return $settings;
		}

		/**
		 * Checking if we are on Settings page
		 *
		 * @access public
		 *
		 * @return boolean
		 */
		public function check_screen() {
			$screen = get_current_screen();
			if ($screen->id == 'woocommerce_page_br-' . $this->cc->info['plugin_name']) {
				return true;
			}

			return false;
		}

		/**
		 * Sanitize option function
		 *
		 * @access public
		 *
		 */
		public function sanitize_option($input) {
			$new_input = self::recursive_array_set($this->cc->defaults, $input);
			return apply_filters('brfr_sanitize_option_' . $this->cc->info['plugin_name'], $new_input, $input, $this->cc->defaults);
		}

		/**
		 * Settings correct values for the array. If it exist in the input it will be used
		 * if not - default will be used
		 *
		 * @access public
		 *
		 */
		public function recursive_array_set($default, $options) {
			$result = [];

			foreach ($default as $key => $value) {
				if (array_key_exists($key, $options)) {
					if (is_array($value)) {
						if (is_array($options[$key])) {
							$result[$key] = self::recursive_array_set($value, $options[$key]);
						} else {
							$result[$key] = self::recursive_array_set($value, []);
						}
					} else {
						$result[$key] = $options[$key];
					}
				} else {
					if (is_array($value)) {
						$result[$key] = self::recursive_array_set($value, []);
					} else {
						$result[$key] = '';
					}
				}
			}

			foreach ($options as $key => $value) {
				if (! array_key_exists($key, $result)) {
					$result[$key] = $value;
				}
			}

			return $result;
		}

		/**
		 * Getting plugin option values
		 *
		 * @access public
		 */
		public function get_option() {
			if (! function_exists('icl_object_id')) {
				$options = wp_cache_get($this->cc->values['settings_name'], 'berocket_framework_option');
			}
			if (empty($options)) {
				$options = get_option($this->cc->values['settings_name']);

				if (! empty($options) && is_array($options)) {
					$options = array_merge($this->cc->defaults, $options);
				} else {
					$options = $this->cc->defaults;
				}
				$options = apply_filters('brfr_get_option_cache_' . $this->cc->info['plugin_name'], $options, $this->cc->defaults);
				wp_cache_set($this->cc->values['settings_name'], $options, 'berocket_framework_option', 600);
			}
			$global_options = $this->get_global_option();
			if (count($this->global_settings)) {
				foreach ($this->global_settings as $global_setting) {
					if (isset($global_options[$global_setting])) {
						$options[$global_setting] = $global_options[$global_setting];
					}
				}
			}

			return apply_filters('brfr_get_option_' . $this->cc->info['plugin_name'], $options, $this->cc->defaults);
		}
		public function get_global_option() {
			$option = get_option('berocket_framework_option_global');
			if (! is_array($option)) {
				$option = [];
			}
			return $option;
		}
		public function save_global_option($option) {
			return update_option('berocket_framework_option_global', $option);
		}
		public function is_settings_page($settings_page) {
			if (! empty($_GET['page']) && $_GET['page'] == $this->cc->values['option_page']) {
				$settings_page = true;
			}
			return $settings_page;
		}
	}
}
