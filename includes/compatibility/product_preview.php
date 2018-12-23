<?php
class BeRocket_Labels_compat_product_preview {
    function __construct() {
        add_action( 'berocket_pp_popup_inside_image', array(__CLASS__, 'show_labels') );
        add_action( 'berocket_pp_popup_inside_thumbnails', array(__CLASS__, 'show_labels') );
        add_action( 'BeRocket_preview_after_general_settings', array(__CLASS__, 'settings'), 10, 2 );
    }
    public static function show_labels($options) {
        if( empty($options['hide_berocket_labels']) ) {
            $BeRocket_products_label = BeRocket_products_label::getInstance();
            $BeRocket_products_label->set_all_label();
        }
    }
    public static function settings($name, $options) {
        echo '
        <tr>
            <th>' . __( 'Hide BeRocket Advanced Labels', 'BeRocket_products_label_domain' ) . '</th>
            <td>
                <input type="checkbox" name="' . $name . '[hide_berocket_labels]"' . (empty($options['hide_berocket_labels']) ? '' : 'checked') . '>
            </td>
        </tr>';
    }
}
new BeRocket_Labels_compat_product_preview();
