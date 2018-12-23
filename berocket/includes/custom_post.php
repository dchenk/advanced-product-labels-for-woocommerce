<?php
/* $post_settings example
array(
   'labels' => array(
	   'menu_name'          => _x( 'Product Filters', 'Admin menu name', 'BeRocket_AJAX_domain' ),
	   'add_new_item'       => __( 'Add New Filter', 'BeRocket_AJAX_domain' ),
	   'edit'               => __( 'Edit', 'BeRocket_AJAX_domain' ),
	   'edit_item'          => __( 'Edit Filter', 'BeRocket_AJAX_domain' ),
	   'new_item'           => __( 'New Filter', 'BeRocket_AJAX_domain' ),
	   'view'               => __( 'View Filters', 'BeRocket_AJAX_domain' ),
	   'view_item'          => __( 'View Filter', 'BeRocket_AJAX_domain' ),
	   'search_items'       => __( 'Search Product Filters', 'BeRocket_AJAX_domain' ),
	   'not_found'          => __( 'No Product Filters found', 'BeRocket_AJAX_domain' ),
	   'not_found_in_trash' => __( 'No Product Filters found in trash', 'BeRocket_AJAX_domain' ),
   ),
   'description'     => __( 'This is where you can add Product Filters.', 'BeRocket_AJAX_domain' ),
   'public'          => true,
   'show_ui'         => true,
   'capability_type' => 'post',
   'publicly_queryable'  => false,
   'exclude_from_search' => true,
   'show_in_menu'        => 'edit.php?post_type=product',
   'hierarchical'        => false,
   'rewrite'             => false,
   'query_var'           => false,
   'supports'            => array( 'title' ),
   'show_in_nav_menus'   => false,
)
*/

if (!class_exists('BeRocket_custom_post_class')) {

	class BeRocket_custom_post_class {
		public $meta_boxes = [];
		public $default_settings = [];
		public $post_settings;
		public $post_name;
		protected static $instance;

		public function __construct() {
			if (null === static::$instance) {
				static::$instance = $this;
			}
			add_filter('init', [$this, 'init']);
			add_filter('admin_init', [$this, 'admin_init']);
		}

		public static function getInstance() {
			if (null === static::$instance) {
				static::$instance = new static();
			}
			return static::$instance;
		}

		public function init() {
			$this->default_settings = apply_filters('berocket_custom_post_' . $this->post_name . '_default_settings', $this->default_settings, self::$instance);
			register_post_type($this->post_name, $this->post_settings);
		}

		public function get_custom_posts($args = []) {
			$args = array_merge([
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
			], $args);
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
			add_filter('manage_edit-' . $this->post_name . '_columns', [$this, 'manage_edit_columns']);
			add_action('manage_' . $this->post_name . '_posts_custom_column', [$this, 'columns_replace'], 2);
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

		public function manage_edit_columns($columns) {
			$columns = [];
			$columns["cb"]   = '<input type="checkbox" />';
			$columns["name"] = __("Name", 'BeRocket_domain');
			return $columns;
		}

		public function columns_replace($column) {
			global $post;
			switch ($column) {
				case "name":

					$edit_link = get_edit_post_link($post->ID);
					$title = '<a class="row-title" href="' . $edit_link . '">' . _draft_or_post_title() . '</a>';

					echo 'ID:' . $post->ID . ' <strong>' . $title . '</strong>';

					break;
			}
		}

		public function add_meta_boxes() {
			add_meta_box('submitdiv', __('Save content', 'BeRocket_domain'), [$this, 'save_meta_box'], $this->post_name, 'side', 'high');
			add_meta_box('copysettingsfromdiv', __('Copy settings from', 'BeRocket_domain'), [$this, 'copy_settings_from'], $this->post_name, 'side', 'high');
			foreach ($this->meta_boxes as $meta_box) {
				add_meta_box($meta_box['slug'], $meta_box['name'], $meta_box['callback'], $this->post_name, $meta_box['position'], $meta_box['priority']);
			}
		}

		public function copy_settings_from($post) {
			$posts_array = $this->get_custom_posts(); ?>
            <div class="berocket_copy_from_custom_post_block">
                <?php do_action('berocket_copy_from_custom_post_block', $this->post_name, $post); ?>
                <select name="berocket_copy_from_custom_post_select">
                    <option value="0"><?php _e('Do not copy', 'BeRocket_domain'); ?></option>
                    <?php
					if (!empty($posts_array) && is_array($posts_array)) {
						foreach ($posts_array as $post_id) {
							if ($post_id == $post->ID) {
								continue;
							}
							echo '<option value="' . $post_id . '">(ID: ' . $post_id . ') ' . get_the_title($post_id) . '</option>';
						}
					} ?>
                </select>
                <input name="berocket_copy_from_custom_post" type="hidden" value="">
                <button type="button" class="button" disabled><?php _e('Copy', 'BeRocket_domain'); ?></button>
            </div>
            <script>
				jQuery('.berocket_copy_from_custom_post_block button').on('click', function() {
					jQuery('.berocket_copy_from_custom_post_block input').val(jQuery('.berocket_copy_from_custom_post_block select').val());
					jQuery('.submitbox input[type=submit]').trigger('click');
				});
				jQuery('.berocket_copy_from_custom_post_block select').on('change', function() {
					jQuery('.berocket_copy_from_custom_post_block button').prop('disabled', ( ! jQuery(this).val() || jQuery(this).val() == '0' ));
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
			wp_enqueue_style('font-awesome'); ?>
			<div class="submitbox" id="submitpost">

				<div id="minor-publishing">
					<div id="major-publishing-actions">
						<div id="delete-action"><?php
						if (!in_array($pagenow, ['post-new.php'], true) && current_user_can("delete_post", $post->ID)) {
							if (!EMPTY_TRASH_DAYS) {
								$delete_text = __('Delete Permanently', 'BeRocket_domain');
							} else {
								$delete_text = __('Move to Trash', 'BeRocket_domain');
							} ?>
							<a class="submitdelete deletion" href="<?php echo esc_url(get_delete_post_link($post->ID)); ?>"><?php echo esc_attr($delete_text); ?></a>
							<?php
						} ?>
						</div>

						<div id="publishing-action">
							<span class="spinner"></span>
							<input type="submit" class="button button-primary tips" name="publish" value="<?php _e('Save', 'BeRocket_domain'); ?>" data-tip="<?php _e('Save/update notice', 'BeRocket_domain'); ?>" />
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
			<?php
			wp_nonce_field($this->post_name . '_check', $this->post_name . '_nonce');
		}

		public function wc_save_check($post_id, $post): bool {
			if ($this->post_name != $post->post_type) {
				return false;
			}

			$current_settings = get_post_meta($post_id, $this->post_name, true);

			if (empty($current_settings)) {
				update_post_meta($post_id, $this->post_name, $this->default_settings);
			}

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return false;
			}

			if (empty($_REQUEST[$this->post_name . '_nonce']) || ! wp_verify_nonce($_REQUEST[$this->post_name . '_nonce'], $this->post_name . '_check')) {
				return false;
			}

			return true;
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
