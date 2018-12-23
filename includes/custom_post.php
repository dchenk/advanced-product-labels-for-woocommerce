<?php
class BeRocket_conditions_advanced_labels extends BeRocket_conditions {
}
class BeRocket_advanced_labels_custom_post extends BeRocket_custom_post_class {
    public $hook_name = 'berocket_advanced_label_editor';
    public $conditions;
    protected static $instance;
    function __construct() {
        add_action('products_label_framework_construct', array($this, 'init_conditions'));
        $this->post_name = 'br_labels';
        $this->post_settings = array(
            'label' => __( 'Advanced Label', 'BeRocket_products_label_domain' ),
            'labels' => array(
                'name'               => __( 'Advanced Label', 'BeRocket_products_label_domain' ),
                'singular_name'      => __( 'Advanced Label', 'BeRocket_products_label_domain' ),
                'menu_name'          => _x( 'Advanced Labels', 'Admin menu name', 'BeRocket_products_label_domain' ),
                'add_new'            => __( 'Add Label', 'BeRocket_products_label_domain' ),
                'add_new_item'       => __( 'Add New Label', 'BeRocket_products_label_domain' ),
                'edit'               => __( 'Edit', 'BeRocket_products_label_domain' ),
                'edit_item'          => __( 'Edit Label', 'BeRocket_products_label_domain' ),
                'new_item'           => __( 'New Label', 'BeRocket_products_label_domain' ),
                'view'               => __( 'View Labels', 'BeRocket_products_label_domain' ),
                'view_item'          => __( 'View Label', 'BeRocket_products_label_domain' ),
                'search_items'       => __( 'Search Advanced Labels', 'BeRocket_products_label_domain' ),
                'not_found'          => __( 'No Advanced Labels found', 'BeRocket_products_label_domain' ),
                'not_found_in_trash' => __( 'No Advanced Labels found in trash', 'BeRocket_products_label_domain' ),
            ),
            'description'     => __( 'This is where you can add advanced labels.', 'BeRocket_products_label_domain' ),
            'public'          => true,
            'show_ui'         => true,
            'map_meta_cap'    => true,
            'capability_type' => 'product',
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'show_in_menu'        => 'berocket_account',
            'hierarchical'        => false,
            'rewrite'             => false,
            'query_var'           => false,
            'supports'            => array( 'title' ),
            'show_in_nav_menus'   => false,
        );
        $this->default_settings = array(
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
            'data'                  => array(),
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
        );
        $this->add_meta_box('conditions', __( 'Conditions', 'BeRocket_products_label_domain' ));
        $this->add_meta_box('settings', __( 'Advanced Labels Settings', 'BeRocket_products_label_domain' ));
        $this->add_meta_box('description', __( 'Description', 'BeRocket_products_label_domain' ), false, 'side');
        $this->add_meta_box('preview', __( 'Preview', 'BeRocket_products_label_domain' ), false, 'side');
        add_filter('brfr_berocket_advanced_label_editor_custom_css_explanation', array(__CLASS__, 'section_custom_css_explanation'), 10, 4);
        parent::__construct();
    }
    public function init_conditions() {
        $this->conditions = new BeRocket_conditions_advanced_labels($this->post_name.'[data]', $this->hook_name, array(
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
        ));
    }
    public function conditions($post) {
        $options = $this->get_option( $post->ID );
        if( empty($options['data']) ) {
            $options['data'] = array();
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
        wp_enqueue_style( 'berocket_tippy' );
        wp_enqueue_script( 'berocket_tippy');
        ?>
        <div class="berocket_label_preview_wrap">
            <div class="berocket_label_preview">
                <img class="berocket_product_image" src="<?php echo plugin_dir_url(__FILE__).'../images/labels.png'; ?>">
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
    public function settings($post) {
        wp_enqueue_script( 'berocket_products_label_admin', plugins_url( '../js/admin.js', __FILE__ ), array( 'jquery' ), BeRocket_products_label_version );
        wp_enqueue_script( 'berocket_framework_admin' );
        wp_enqueue_style( 'berocket_framework_admin_style' );
        wp_enqueue_script( 'berocket_widget-colorpicker' );
        wp_enqueue_style( 'berocket_widget-colorpicker-style' );
        wp_enqueue_style( 'berocket_font_awesome' );
        $options = $this->get_option( $post->ID );
        $BeRocket_products_label_var = BeRocket_products_label::getInstance();
        echo '<div class="br_framework_settings br_alabel_settings">';
        $BeRocket_products_label_var->display_admin_settings(
            array(
                'General' => array(
                    'icon' => 'cog',
                ),
                'Style'     => array(
                    'icon' => 'css3',
                ),
                'Position'     => array(
                    'icon' => 'arrows',
                ),
                'Tooltip'     => array(
                    'icon' => 'comment',
                ),
                'Custom CSS'   => array(
                    'icon' => 'css3',
                ),
            ),
            array(
                'General' => array(
                    'content_type' => array(
                        "type"     => "selectbox",
                        "options"  => array(
                            array('value' => 'text', 'text' => __('Text', 'BeRocket_products_label_domain')),
                            array('value' => 'sale_p', 'text' => __('Discount percentage', 'BeRocket_products_label_domain')),
                            array('value' => 'price', 'text' => __('Price', 'BeRocket_products_label_domain')),
                            array('value' => 'stock_status', 'text' => __('Stock Status', 'BeRocket_products_label_domain')),
                        ),
                        "class"    => 'berocket_label_content_type',
                        "label"    => __('Content type', 'BeRocket_products_label_domain'),
                        "name"     => "content_type",
                        "value"    => $options['content_type'],
                    ),
                    'text' => array(
                        "type"     => "text",
                        "label"    => __('Text', 'BeRocket_products_label_domain'),
                        "class"    => 'berocket_label_ berocket_label_text',
                        "name"     => "text",
                        "value"    => $options['text'],
                    ),
                    'text_before' => array(
                        "label"    => __('Text Before', 'BeRocket_products_label_domain'),
                        "items"    => array(
                            'text_before' => array(
                                "type"     => "text",
                                "class"    => 'berocket_label_ berocket_label_sale_p',
                                "label_be_for" => __('Text', 'BeRocket_products_label_domain'),
                                "name"     => "text_before",
                                "value"    => $options['text_before'],
                            ),
                            "text_before_nl" =>array(
                                "type"     => "checkbox",
                                "label_for" => __('New Line', 'BeRocket_products_label_domain'),
                                "name"     => "text_before_nl",
                                "value"    => "1",
                                "selected" => false
                            ),
                        )
                    ),
                    'text_after' => array(
                        "label"    => __('Text After', 'BeRocket_products_label_domain'),
                        "items"    => array(
                            'text_after' => array(
                                "type"     => "text",
                                "class"    => 'berocket_label_ berocket_label_sale_p',
                                "label_be_for" => __('Text', 'BeRocket_products_label_domain'),
                                "name"     => "text_after",
                                "value"    => $options['text_after'],
                            ),
                            "text_before_nl" =>array(
                                "type"     => "checkbox",
                                "label_for" => __('New Line', 'BeRocket_products_label_domain'),
                                "name"     => "text_after_nl",
                                "value"    => "1",
                                "selected" => false
                            ),
                        )
                    ),
                    'discount_minus' => array(
                        "type"     => "checkbox",
                        "label"    => __('Use minus symbol', 'BeRocket_products_label_domain'),
                        "class"    => 'berocket_label_ berocket_label_sale_p',
                        "name"     => "discount_minus",
                        "value"    => "1",
                        "selected" => false
                    ),
                ),
                'Style'     => array(
                    /*'templates' => array(
                        "section"  => "templates",
                        "label"    => __('Templates', 'BeRocket_products_label_domain'),
                        "name"     => "css_template",
                        "value"    => $options['template'],
                    ),*/
                    'color_use' => array(
                        "type"     => "checkbox",
                        "label"    => __('Use background color', 'BeRocket_products_label_domain'),
                        "class"    => 'br_label_backcolor_use br_js_change',
                        "name"     => "color_use",
                        "value"    => "1",
                        "extra"    => ' data-for=".br_alabel > span" data-style="use:background-color" data-ext=""',
                        "selected" => false
                    ),
                    'color' => array(
                        "type"     => "color",
                        "label"    => __('Background color', 'BeRocket_products_label_domain'),
                        "name"     => "color",
                        "class"    => 'br_label_backcolor br_js_change',
                        "extra"    => ' data-for=".br_alabel > span" data-style="background-color" data-ext=""',
                        "value"    => $options['color'],
                    ),
                    'font_color' => array(
                        "type"     => "color",
                        "label"    => __('Font color', 'BeRocket_products_label_domain'),
                        "name"     => "font_color",
                        "class"    => 'berocket_label_ berocket_label_text berocket_label_sale_end berocket_label_sale_p br_js_change',
                        "extra"    => ' data-for=".br_alabel > span" data-style="color" data-ext=""',
                        "value"    => $options['font_color'],
                    ),
                    'border_radius' => array(
                        "type"     => "text",
                        "label"    => __('Border radius', 'BeRocket_products_label_domain'),
                        "name"     => "border_radius",
                        "class"    => "br_js_change",
                        "extra"    => ' data-for=".br_alabel > span" data-style="border-radius" data-ext="px" data-notext="px,em,%"',
                        "value"    => '10',
                    ),
                    'line_height' => array(
                        "type"     => "number",
                        "label"    => __('Line height', 'BeRocket_products_label_domain'),
                        "name"     => "line_height",
                        "class"    => "br_js_change",
                        "extra"    => ' min="0" max="400" data-for=".br_alabel > span" data-style="line-height" data-ext="px"',
                        "value"    => $options['line_height'],
                    ),
                    'image_height' => array(
                        "type"     => "number",
                        "label"    => __('Height', 'BeRocket_products_label_domain'),
                        "name"     => "image_height",
                        "class"    => "br_js_change",
                        "extra"    => ' data-for=".br_alabel > span" data-style="height" data-ext="px"',
                        "value"    => $options['image_height'],
                    ),
                    'image_width' => array(
                        "type"     => "number",
                        "label"    => __('Width', 'BeRocket_products_label_domain'),
                        "name"     => "image_width",
                        "class"    => "br_js_change",
                        "extra"    => ' data-for=".br_alabel > span" data-style="width" data-ext="px"',
                        "value"    => $options['image_width'],
                    ),
                ),
                'Position'     => array(
                    'type' => array(
                        "type"     => "selectbox",
                        "options"  => array(
                            array('value' => 'label', 'text' => __('Label', 'BeRocket_products_label_domain')),
                            array('value' => 'image', 'text' => __('On image', 'BeRocket_products_label_domain')),
                        ),
                        "class"    => 'berocket_label_type_select',
                        "label"    => __('Type', 'BeRocket_products_label_domain'),
                        "name"     => "type",
                        "value"    => $options['type'],
                    ),
                    'padding_top' => array(
                        "type"     => "number",
                        "label"    => __('Padding from top', 'BeRocket_products_label_domain'),
                        "class"    => 'berocket_label_type_ berocket_label_type_image br_js_change',
                        "name"     => "padding_top",
                        "extra"    => ' data-for=".br_alabel" data-style="top" data-ext="px"',
                        "value"    => $options['padding_top'],
                    ),
                    'padding_horizontal' => array(
                        "type"     => "number",
                        "label"    => '<span class="pos__ pos__left">' . __('Padding from left: ', 'BeRocket_products_label_domain') . '</span><span class="pos__ pos__right">' . __('Padding from right: ', 'BeRocket_products_label_domain') . '</span>',
                        "class"    => 'berocket_label_type_ berocket_label_type_image pos_label_ pos_label_right pos_label_left br_js_change',
                        "name"     => "padding_horizontal",
                        "extra"    => ' data-for=".br_alabel" data-from=".pos_label" data-ext="px"',
                        "value"    => $options['padding_horizontal'],
                    ),
                    'position' => array(
                        "type"     => "selectbox",
                        "options"  => array(
                            array('value' => 'left', 'text' => __('Left', 'BeRocket_products_label_domain')),
                            array('value' => 'center', 'text' => __('Center', 'BeRocket_products_label_domain')),
                            array('value' => 'right', 'text' => __('Right', 'BeRocket_products_label_domain')),
                        ),
                        "class"    => 'pos_label',
                        "label"    => __('Position', 'BeRocket_products_label_domain'),
                        "name"     => "position",
                        "value"    => $options['position'],
                    ),
                ),
                'Tooltip'   => array(
                    'tooltip_content' => array(
                        'label'    => __('Content', 'BeRocket_products_label_domain'),
                        "type"     => "textarea",
                        "class"    => "berocket_html_tooltip_content",
                        "name"     => "tooltip_content",
                        "value"    => $options['tooltip_content'],
                    ),
                    'tooltip_theme' => array(
                        "type"     => "selectbox",
                        "options"  => array(
                            array('value' => 'dark', 'text' => __('Dark', 'BeRocket_products_label_domain')),
                            array('value' => 'light', 'text' => __('Light', 'BeRocket_products_label_domain')),
                            array('value' => 'translucent', 'text' => __('Translucent', 'BeRocket_products_label_domain')),
                        ),
                        "label"    => __('Style', 'BeRocket_products_label_domain'),
                        "name"     => "tooltip_theme",
                        "value"    => $options['tooltip_theme'],
                    ),
                    'tooltip_position' => array(
                        "type"     => "selectbox",
                        "options"  => array(
                            array('value' => 'top', 'text' => __('Top', 'BeRocket_products_label_domain')),
                            array('value' => 'bottom', 'text' => __('Bottom', 'BeRocket_products_label_domain')),
                            array('value' => 'left', 'text' => __('Left', 'BeRocket_products_label_domain')),
                            array('value' => 'right', 'text' => __('Right', 'BeRocket_products_label_domain')),
                        ),
                        "label"    => __('Position', 'BeRocket_products_label_domain'),
                        "name"     => "tooltip_position",
                        "value"    => $options['tooltip_position'],
                    ),
                    'tooltip_open_delay' => array(
                        "type"     => "number",
                        "label"    => __('Open delay', 'BeRocket_products_label_domain'),
                        "name"     => "tooltip_open_delay",
                        "extra"    => 'min="0"',
                        "value"    => $options['tooltip_open_delay'],
                    ),
                    'tooltip_close_delay' => array(
                        "type"     => "number",
                        "label"    => __('Close delay', 'BeRocket_products_label_domain'),
                        "name"     => "tooltip_close_delay",
                        "extra"    => 'min="0"',
                        "value"    => $options['tooltip_close_delay'],
                    ),
                    'tooltip_open_on' => array(
                        "type"     => "selectbox",
                        "options"  => array(
                            array('value' => 'mouseenter', 'text' => __('Hover', 'BeRocket_products_label_domain')),
                            array('value' => 'click', 'text' => __('Click', 'BeRocket_products_label_domain')),
                        ),
                        "label"    => __('Open on', 'BeRocket_products_label_domain'),
                        "name"     => "tooltip_open_on",
                        "value"    => $options['tooltip_open_on'],
                    ),
                    'tooltip_close_on_click' => array(
                        "type"     => "checkbox",
                        "label"    => __('Close on click everywhere', 'BeRocket_products_label_domain'),
                        "name"     => "tooltip_close_on_click",
                        "value"    => '1',
                    ),
                    'tooltip_use_arrow' => array(
                        "type"     => "checkbox",
                        "label"    => __('Use arrow', 'BeRocket_products_label_domain'),
                        "name"     => "tooltip_use_arrow",
                        "value"    => '1',
                    ),
                    'tooltip_max_width' => array(
                        "type"     => "number",
                        "label"    => __('Max width', 'BeRocket_products_label_domain'),
                        "name"     => "tooltip_max_width",
                        "extra"    => 'min="0"',
                        "value"    => $options['tooltip_max_width'],
                    ),
                ),
                'Custom CSS' => array(
                    'custom_css_explanation' => array(
                        "section" => "custom_css_explanation",
                    ),
                    'div_custom_class' => array(
                        "type"     => "text",
                        "label"    => __('&lt;div&gt; block custom class', 'BeRocket_products_label_domain'),
                        "name"     => "div_custom_class",
                        "value"    => $options['div_custom_class'],
                    ),
                    'div_custom_css' => array(
                        "type"     => "textarea",
                        "label"    => __('&lt;div&gt; block custom CSS', 'BeRocket_products_label_domain'),
                        "name"     => "div_custom_css",
                        "value"    => $options['div_custom_css'],
                    ),
                    'span_custom_class' => array(
                        "type"     => "text",
                        "label"    => __('&lt;span&gt; block custom class', 'BeRocket_products_label_domain'),
                        "name"     => "span_custom_class",
                        "value"    => $options['span_custom_class'],
                    ),
                    'span_custom_css' => array(
                        "type"     => "textarea",
                        "label"    => __('&lt;span&gt; block custom CSS', 'BeRocket_products_label_domain'),
                        "name"     => "span_custom_css",
                        "value"    => $options['span_custom_css'],
                    ),
                    'b_custom_class' => array(
                        "type"     => "text",
                        "label"    => __('&lt;b&gt; block custom class', 'BeRocket_products_label_domain'),
                        "name"     => "b_custom_class",
                        "value"    => $options['b_custom_class'],
                    ),
                    'b_custom_css' => array(
                        "type"     => "textarea",
                        "label"    => __('&lt;b&gt; block custom CSS', 'BeRocket_products_label_domain'),
                        "name"     => "b_custom_css",
                        "value"    => $options['b_custom_css'],
                    ),
                    'i1_custom_class' => array(
                        "type"     => "text",
                        "label"    => __('1) &lt;i&gt; block custom class', 'BeRocket_products_label_domain'),
                        "name"     => "i1_custom_class",
                        "value"    => $options['i1_custom_class'],
                    ),
                    'i1_custom_css' => array(
                        "type"     => "textarea",
                        "label"    => __('1) &lt;i&gt; block custom CSS', 'BeRocket_products_label_domain'),
                        "name"     => "i1_custom_css",
                        "value"    => $options['i1_custom_css'],
                    ),
                    'i2_custom_class' => array(
                        "type"     => "text",
                        "label"    => __('2) &lt;i&gt; block custom class', 'BeRocket_products_label_domain'),
                        "name"     => "i2_custom_class",
                        "value"    => $options['i2_custom_class'],
                    ),
                    'i2_custom_css' => array(
                        "type"     => "textarea",
                        "label"    => __('2) &lt;i&gt; block custom CSS', 'BeRocket_products_label_domain'),
                        "name"     => "i2_custom_css",
                        "value"    => $options['i2_custom_css'],
                    ),
                    'i3_custom_class' => array(
                        "type"     => "text",
                        "label"    => __('3) &lt;i&gt; block custom class', 'BeRocket_products_label_domain'),
                        "name"     => "i3_custom_class",
                        "value"    => $options['i3_custom_class'],
                    ),
                    'i3_custom_css' => array(
                        "type"     => "textarea",
                        "label"    => __('3) &lt;i&gt; block custom CSS', 'BeRocket_products_label_domain'),
                        "name"     => "i3_custom_css",
                        "value"    => $options['i3_custom_css'],
                    ),
                    'i4_custom_class' => array(
                        "type"     => "text",
                        "label"    => __('4) &lt;i&gt; block custom class', 'BeRocket_products_label_domain'),
                        "name"     => "i4_custom_class",
                        "value"    => $options['i4_custom_class'],
                    ),
                    'i4_custom_css' => array(
                        "type"     => "textarea",
                        "label"    => __('4) &lt;i&gt; block custom CSS', 'BeRocket_products_label_domain'),
                        "name"     => "i4_custom_css",
                        "value"    => $options['i4_custom_css'],
                    ),
                ),
            ),
            array(
                'name_for_filters' => $this->hook_name,
                'hide_header' => true,
                'hide_form' => true,
                'hide_additional_blocks' => true,
                'hide_save_button' => true,
                'settings_name' => $this->post_name,
                'options' => $options
            )
        );
        echo '</div>';
        ?>
        <style>
        .berocket-label-margin-paddings-block {
            width: 50px;
        }
        </style>
        <?php
    }

    public static function section_custom_css_explanation($html, $item, $options, $name) {
        $html .= '<tr><td colspan="2">It is settings for advanced users. Please do not use it if you don\'t know how it work.<br>
        This options is provided for designer and programmers.<br>
        How labels looks in HTML<br>
        &lt;div&gt;<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&lt;span&gt;<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;i&gt;&lt;/i&gt;<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;i&gt;&lt;/i&gt;<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;i&gt;&lt;/i&gt;<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;i&gt;&lt;/i&gt;<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;b&gt;TEXT OF LABEL&lt;/b&gt;<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&lt;/span&gt;<br>
        &lt;/div&gt;</td></tr>';
        return $html;
    }

    public function get_option( $post_id ) {
        $options_test = get_post_meta( $post_id, $this->post_name, true );
        if( empty($options_test) ) {
            $this->post_name = 'br_label';
        }
        $options = parent::get_option( $post_id );
        if( empty($options_test) ) {
            $this->post_name = 'br_labels';
            update_post_meta( $post_id, $this->post_name, $options );
        }
        return $options;
    }
    public function wc_save_check($post_id, $post) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return false;
        }
        if ( $this->post_name != $post->post_type && 'product' != $post->post_type ) {
            return false;
        }
        if( empty($_REQUEST[$this->post_name.'_nonce']) || ! wp_verify_nonce($_REQUEST[$this->post_name.'_nonce'], $this->post_name.'_check') ) {
            return false;
        }
        return true;
    }
    public function wc_save_product( $post_id, $post ) {
        $current_settings = get_post_meta( $post_id, $this->post_name, true );
        if( empty($current_settings) ) {
            update_post_meta( $post_id, $this->post_name, $this->default_settings );
        }
        if( ! $this->wc_save_check($post_id, $post) ) {
            return;
        }
        if( ! isset($_POST['br_labels']['color_use']) ) {
            $_POST['br_labels']['color_use'] = 0;
        }
        $_POST['br_labels'] = apply_filters('berocket_apl_wc_save_product', $_POST['br_labels'], $post_id);
        parent::wc_save_product( $post_id, $post );
    }
    public function manage_edit_columns ( $columns ) {
        $columns = parent::manage_edit_columns($columns);
        $columns["products"] = __( "Label text", 'BeRocket_products_label_domain' );
        $columns["data"] = __( "Position", 'BeRocket_products_label_domain' );
        return $columns;
    }
    public function columns_replace ( $column ) {
        parent::columns_replace($column);
        global $post;
        $label_type = $this->get_option($post->ID);
        switch ( $column ) {
            case "products":
                $text = '';
                if( isset($label_type['text']) ) {
                    $text = $label_type['text'];
                }
                if( $label_type['content_type'] == 'sale_p' ) {
                    $text = __('Discount percentage', 'BeRocket_products_label_domain');
                }
                echo apply_filters('berocket_labels_products_column_text', $text, $label_type);
                break;
            case "data":
                $position = array('left' => __('Left', 'BeRocket_products_label_domain'), 'center' => __('Center', 'BeRocket_products_label_domain'), 'right' => __('Right', 'BeRocket_products_label_domain'));
                $type = array('image' => __('On image', 'BeRocket_products_label_domain'), 'label' => __('Label', 'BeRocket_products_label_domain'));
                if( isset($label_type['position']) && isset($label_type['type']) ) {
                    echo $type[$label_type['type']].' ( '.$position[$label_type['position']].' )';
                }
                break;
        }
    }
}
new BeRocket_advanced_labels_custom_post();
