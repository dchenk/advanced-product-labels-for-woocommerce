<?php

if (!class_exists('BeRocket_custom_post_class')) {
	class BeRocket_custom_post_class {
		public $meta_boxes = [];
		public $default_settings = [];
		public $post_settings;
		public $post_name;
		protected static $instance;

		public function __construct() {
			if (static::$instance === null) {
				static::$instance = $this;
			}
			add_filter('init', [$this, 'init']);
			add_filter('admin_init', [$this, 'admin_init']);
		}

		public static function getInstance(): BeRocket_advanced_labels_custom_post {
			if (static::$instance === null) {
				static::$instance = new static();
			}
			return static::$instance;
		}

		public function init() {
			$this->default_settings = apply_filters('berocket_custom_post_' . $this->post_name . '_default_settings', $this->default_settings, self::$instance);
			register_post_type($this->post_name, $this->post_settings);
		}

		public function get_custom_posts() {
			$args = [
				'posts_per_page'   => -1,
				'offset'           => 0,
				'category'         => '',
				'category_name'    => '',
				'include'          => '',
				'exclude'          => '',
				'meta_key'         => '',
				'meta_value'       => '',
				'post_type'        => $this->post_name,
				'post_mime_type'   => '',
				'post_parent'      => '',
				'author'           => '',
				'post_status'      => 'publish',
				'fields'           => 'ids',
				'suppress_filters' => false,
			];
			$posts_array = new WP_Query($args);
			return $posts_array->posts;
		}

		public function add_meta_box($slug, $name, $callback = false, $position = 'normal', $priority = 'high') {
			if ($callback === false) {
				$callback = [$this, $slug];
			}
			$this->meta_boxes[$slug] = ['slug' => $slug, 'name' => $name, 'callback' => $callback, 'position' => $position, 'priority' => $priority];
		}

		public function admin_init() {
			add_filter('bulk_actions-edit-' . $this->post_name, [$this, 'bulk_actions_edit']);
			add_filter('views_edit-' . $this->post_name, [$this, 'views_edit']);
			add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
			add_action('save_post', [$this, 'wc_save_product'], 10, 2);
			add_filter('post_row_actions', [$this, 'post_row_actions'], 10, 2);
			add_filter('list_table_primary_column', [$this, 'list_table_primary_column'], 10, 2);
		}

		public function post_row_actions($actions, $post) {
			if ($post->post_type == $this->post_name) {
				if (isset($actions['inline hide-if-no-js'])) {
					unset($actions['inline hide-if-no-js']);
				}
			}
			return $actions;
		}

		public function list_table_primary_column($default, $screen_id) {
			if ($screen_id == 'edit-' . $this->post_name) {
				$default = 'name';
			}
			return $default;
		}

		public function bulk_actions_edit($actions) {
			unset($actions['edit']);
			return $actions;
		}

		public function views_edit($view) {
			unset($view['publish'], $view['private'], $view['future']);
			return $view;
		}

		public function add_meta_boxes() {
			add_meta_box('submitdiv', __('Status', 'advanced_product_labels'), [$this, 'save_meta_box'], $this->post_name, 'side', 'high');
			add_meta_box('copysettingsfromdiv', __('Copy settings from', 'advanced_product_labels'), [$this, 'copy_settings_from'], $this->post_name, 'side', 'high');
			foreach ($this->meta_boxes as $meta_box) {
				add_meta_box($meta_box['slug'], $meta_box['name'], $meta_box['callback'], $this->post_name, $meta_box['position'], $meta_box['priority']);
			}
		}

		public function copy_settings_from($post) {
			$posts_array = $this->get_custom_posts(); ?>
			<div class="berocket_copy_from_custom_post_block">
				<?php do_action('berocket_copy_from_custom_post_block', $this->post_name, $post); ?>
				<select id="berocket_copy_from_custom_post_select">
					<option value="0"><?php _e('Do not copy', 'advanced_product_labels'); ?></option>
					<?php
					if (!empty($posts_array) && is_array($posts_array)) {
						foreach ($posts_array as $postID) {
							if ($postID != $post->ID) {
								echo '<option value="' . $postID . '">(ID: ' . $postID . ') ' . get_the_title($postID) . '</option>';
							}
						}
					} ?>
				</select>
				<input name="berocket_copy_from_custom_post" type="hidden">
				<button type="button" class="button" disabled><?php _e('Copy', 'advanced_product_labels'); ?></button>
			</div>
			<script>
				const copyPostSelect = jQuery("#berocket_copy_from_custom_post_select");
				jQuery(".berocket_copy_from_custom_post_block button").on("click", function() {
					jQuery(".berocket_copy_from_custom_post_block input").val(copyPostSelect.val());
					jQuery("#submitpost input[type=submit]").trigger("click");
				});
				copyPostSelect.on("change", function() {
					jQuery(".berocket_copy_from_custom_post_block button").prop("disabled", !jQuery(this).val() || jQuery(this).val() === "0");
				});
			</script>
			<?php
		}

		public function save_meta_box($post) {
			global $pagenow;

			wp_enqueue_script('berocket_aapf_widget-colorpicker');
			wp_enqueue_script('berocket_aapf_widget-admin');
			wp_enqueue_style('brjsf-ui');
			wp_enqueue_script('brjsf-ui');
			wp_enqueue_script('berocket_framework_admin');
			wp_enqueue_style('berocket_framework_admin_style');
			wp_enqueue_script('berocket_widget-colorpicker');
			wp_enqueue_style('berocket_widget-colorpicker-style');
			wp_enqueue_style('font-awesome');
			wp_nonce_field('br_labels_check', $this->post_name . '_nonce'); ?>
			<div class="submitbox" id="submitpost">
				<div id="minor-publishing">
					<div id="major-publishing-actions">
						<div id="delete-action"><?php
						if (strpos($pagenow, 'post-new.php') === false && current_user_can('delete_post', $post->ID)) {
							if (!EMPTY_TRASH_DAYS) {
								$delete_text = __('Delete Permanently', 'advanced_product_labels');
							} else {
								$delete_text = __('Move to Trash', 'advanced_product_labels');
							} ?>
							<a class="submitdelete deletion" href="<?php echo esc_url(get_delete_post_link($post->ID)); ?>"><?php echo esc_attr($delete_text); ?></a>
							<?php
						} ?>
						</div>
						<div id="publishing-action">
							<span class="spinner"></span>
							<input type="submit" class="button button-primary tips" name="publish" value="<?php _e('Save', 'advanced_product_labels'); ?>">
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
			<?php
		}

		public function wc_save_check($postID, $post): bool {
			if ($this->post_name !== $post->post_type) {
				return false;
			}

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return false;
			}

			$current_settings = get_post_meta($postID, $this->post_name, true);

			if (empty($current_settings)) {
				update_post_meta($postID, $this->post_name, $this->default_settings);
			}

			return !empty($_REQUEST[$this->post_name . '_nonce']) && wp_verify_nonce($_REQUEST[$this->post_name . '_nonce'], 'br_labels_check');
		}

		public function wc_save_product($post_id, $post) {
			if ($this->wc_save_check($post_id, $post)) {
				$this->wc_save_product_without_check($post_id, $post);
			}
		}

		public function wc_save_product_without_check($post_id, $post) {
			if (isset($_POST[$this->post_name])) {
				$post_data = berocket_sanitize_array($_POST[$this->post_name]);

				if (is_array($post_data)) {
					$settings = array_merge($this->default_settings, $post_data);
				} else {
					$settings = $post_data;
				}

				update_post_meta($post_id, $this->post_name, $settings);
			}

			if (!empty($_POST['berocket_copy_from_custom_post'])) {
				$copy_option = get_post_meta($_POST['berocket_copy_from_custom_post'], $this->post_name, true);

				if (!empty($copy_option)) {
					update_post_meta($post_id, $this->post_name, $copy_option);
					$_POST[$this->post_name] = $copy_option;
					do_action('berocket_copy_from_custom_post', $this->post_name, $post_id, $post);
				}
			}
		}

		public function get_option($post_id): array {
			$options = get_post_meta($post_id, $this->post_name, true);

			if (!is_array($options)) {
				$options = [];
			}

			return array_merge($this->default_settings, $options);
		}
	}
}
