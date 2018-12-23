<?php
if ( ! class_exists( 'BeRocket_updater' ) ) {
    define( "BeRocket_update_path", 'https://berocket.com/' );
    define( "BeRocket_updater_log", true );
    include_once( plugin_dir_path( __FILE__ ) . 'error_notices.php' );

    class BeRocket_updater {
        public static $plugin_info = array();
        public static $slugs       = array();
        public static $key         = '';
        public static $error_log   = array();
        public static $debug_mode  = false;

        public static function init() {
            $options          = self::get_options();
            self::$debug_mode = ! empty( $options[ 'debug_mode' ] );
        }

        public static function run() {
            $options          = self::get_options();
            self::$debug_mode = ! empty( $options[ 'debug_mode' ] );
            self::$key        = ( empty( $options[ 'account_key' ] ) ? '' : $options[ 'account_key' ] );

            add_action( 'admin_head', array( __CLASS__, 'scripts' ) );
            add_action( 'admin_menu', array( __CLASS__, 'main_menu_item' ), 1 );
            add_action( 'admin_menu', array( __CLASS__, 'account_page' ), 500 );
            add_action( 'network_admin_menu', array( __CLASS__, 'network_account_page' ) );
            add_action( 'admin_init', array( __CLASS__, 'account_option_register' ) );
            add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__, 'update_check_set' ) );
            add_action( 'install_plugins_pre_plugin-information', array( __CLASS__, 'plugin_info' ), 1 );
            add_action( "wp_ajax_br_test_key", array( __CLASS__, 'test_key' ) );
            add_filter( 'http_request_host_is_external', array( __CLASS__, 'allow_berocket_host' ), 10, 3 );

            if ( BeRocket_updater_log ) {
                add_action( 'admin_footer', array( __CLASS__, 'error_log' ) );
                add_action( 'wp_footer', array( __CLASS__, 'error_log' ) );
            }

            $plugin = array();
            $plugin = apply_filters( 'BeRocket_updater_add_plugin', $plugin );

            if ( ! isset( $options[ 'plugin_key' ] ) || ! is_array( $options[ 'plugin_key' ] ) ) {
                $options[ 'plugin_key' ] = array();
            }

            $update = false;
            foreach ( $plugin as $plug_id => $plug ) {
                self::$slugs[ $plug[ 'id' ] ] = $plug[ 'slug' ];

                if ( isset( $options[ 'plugin_key' ][ $plug[ 'id' ] ] ) && $options[ 'plugin_key' ][ $plug[ 'id' ] ] != '' ) {
                    $plugin[ $plug_id ][ 'key' ] = $options[ 'plugin_key' ][ $plug[ 'id' ] ];
                } elseif ( isset( $plugin[ $plug_id ][ 'key' ] ) && $plugin[ $plug_id ][ 'key' ] != '' ) {
                    $options[ 'plugin_key' ][ $plug[ 'id' ] ] = $plugin[ $plug_id ][ 'key' ];
                    $update                                   = true;
                }
            }

            self::$plugin_info = $plugin;

            if ( $update ) {
                self::set_options( $options );
            }

            add_filter( 'berocket_display_additional_notices', array(
                __CLASS__,
                'berocket_display_additional_notices'
            ) );

            if( ! is_network_admin() ) {
                add_filter( 'custom_menu_order', array( __CLASS__, 'wp_menu_order' ) );
            }

            //ADMIN NOTICE CHECK
            add_filter( 'berocket_admin_notice_is_display_notice', array( __CLASS__, 'admin_notice_is_display_notice' ), 10, 3 );
            add_filter( 'berocket_admin_notice_is_display_notice_priority', array( __CLASS__, 'admin_notice_is_display_notice' ), 10, 3 );
        }

        public static function error_log() {
            if ( self::$debug_mode ) {
                self::$error_log                          = apply_filters( 'BeRocket_updater_error_log', self::$error_log );
                self::$error_log[ 'real_memory_usage' ]   = memory_get_peak_usage( true );
                self::$error_log[ 'script_memory_usage' ] = memory_get_peak_usage( false );
                self::$error_log[ 'memory_limit' ]        = ini_get( 'memory_limit' );
                self::$error_log[ 'WP_DEBUG' ]            = 'WP_DEBUG:' . ( defined( 'WP_DEBUG' ) ? ( WP_DEBUG ? 'true' : 'false' ) : 'false' ) . '; WP_DEBUG_DISPLAY:' . ( defined( 'WP_DEBUG_DISPLAY' ) ? ( WP_DEBUG_DISPLAY ? 'true' : 'false' ) : 'false' );

                ?>
                <script>
                    console.log(<?php echo json_encode( self::$error_log ); ?>);
                </script>
                <?php
            }
        }

        public static function wp_menu_order( $menu_ord ) {
            global $submenu;

            if( empty($submenu[ 'berocket_account' ]) || ! is_array($submenu[ 'berocket_account' ]) || count($submenu[ 'berocket_account' ]) == 0 ) {
                return $menu_ord;
            }

            $new_order_temp = array();
            $new_sub_order  = array();
            $new_order_sort = array();

            $compatibility_hack = apply_filters('BeRocket_updater_menu_order_custom_post', array());

            foreach ( $submenu[ 'berocket_account' ] as $item ) {
                if ( $item[ 0 ] == 'BeRocket' ) {
                    $new_order_temp[] = $item;
                    $new_order_sort[] = "AAA";
                    continue;
                } elseif ( $item[ 0 ] == 'Account Keys' ) {
                    $new_order_temp[] = $item;
                    $new_order_sort[] = "ZZZ";
                    continue;
                }

                if ( false !== strpos( $item[ 2 ], 'edit.php' ) && ! empty($compatibility_hack[ str_replace( "edit.php?post_type=", "", $item[ 2 ] ) ]) ) {
                    $item[ 0 ] = "<span class='berocket_admin_menu_custom_post_submenu'>" . $item[ 0 ] . "</span>";
                    $new_sub_order[ $compatibility_hack[ str_replace( "edit.php?post_type=", "", $item[ 2 ] ) ] ][] = $item;
                } else {
                    $new_order_temp[] = $item;
                    $new_order_sort[] = $item[ 0 ];
                }
            }
            $new_sub_order = apply_filters('BeRocket_updater_menu_order_sub_order', $new_sub_order);

            array_multisort( $new_order_sort, $new_order_temp );

            $new_order = array();
            foreach ( $new_order_temp as $item ) {
                $new_order[] = $item;

                if ( ! empty( $new_sub_order[ $item[ 2 ] ] ) ) {
                    foreach ( $new_sub_order[ $item[ 2 ] ] as $sub_item ) {
                        $new_order[] = $sub_item;
                    }
                }
            }

            $submenu[ 'berocket_account' ] = $new_order;

            return $menu_ord;
        }

        public static function add_to_debug( $data, $plugin, $keys = false ) {
            if ( self::$debug_mode ) {
                if ( $keys === false ) {
                    self::$error_log[ $plugin ][] = $data;
                } elseif ( is_array( $keys ) ) {
                    if ( count( $keys ) > 0 ) {
                        $data_set = self::$error_log[ $plugin ];
                        $last_key = array_pop( $keys );

                        foreach ( $keys as $key ) {
                            if ( ! is_array( $data_set[ $key ] ) ) {
                                $data_set[ $key ] = array();
                            }

                            $new_set = &$data_set[ $key ];
                            unset( $data_set );

                            $data_set = &$new_set;
                            unset( $new_set );
                        }

                        if ( empty( $last_key ) ) {
                            $data_set[] = $data;
                        } else {
                            $data_set[ $last_key ] = $data;
                        }
                    } else {
                        self::$error_log[ $plugin ][] = $data;
                    }
                } else {
                    self::$error_log[ $plugin ][ $keys ] = $data;
                }
            }
        }

        public static function berocket_display_additional_notices( $notices ) {
            if ( ! empty( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'berocket_account' ) {
                return $notices;
            }

            $plugin_count = get_option('berocket_updater_registered_plugins');
            update_option('berocket_updater_registered_plugins', count(self::$plugin_info));
            if( count(self::$plugin_info) != $plugin_count ) {
                $not_activated_notices = false;
            } else {
                if ( is_network_admin() ) {
                    $not_activated_notices = get_site_transient( 'berocket_not_activated_notices_site' );
                } else {
                    $not_activated_notices = get_transient( 'berocket_not_activated_notices' );
                }
            }

            if ( $not_activated_notices == false ) {
                $active_plugin      = get_option( 'berocket_key_activated_plugins' );
                $active_site_plugin = get_site_option( 'berocket_key_activated_plugins' );

                if ( ! is_array( $active_plugin ) ) {
                    $active_plugin = array();
                }

                if ( ! is_array( $active_site_plugin ) ) {
                    $active_site_plugin = array();
                }

                $not_activated_notices = array();
                foreach ( self::$plugin_info as $plugin ) {
                    if ( empty( $active_plugin[ $plugin[ 'id' ] ] ) && empty( $active_site_plugin[ $plugin[ 'id' ] ] ) ) {
                        if ( version_compare( $plugin[ 'version' ], '2.0', '>=' ) ) {
                            $not_activated_notices[] = array(
                                'start'         => 0,
                                'end'           => 0,
                                'name'          => $plugin[ 'name' ],
                                'html'          => '<strong>Please
                                    <a class="berocket_button" href="' . ( is_network_admin() ? admin_url( 'network/admin.php?page=berocket_account' ) : admin_url( 'admin.php?page=berocket_account' ) ) . '">activate plugin</a> ' . $plugin[ 'name' ] . ' with help of Plugin/Account Key from
                                    <a class="berocket_button" href="' . BeRocket_update_path . 'user" target="_blank">BeRocket account</a></strong>.
                                    You can activate plugin in 
                                    <a class="berocket_button" href="' . ( is_network_admin() ? admin_url( 'network/admin.php?page=berocket_account' ) : admin_url( 'admin.php?page=berocket_account' ) ) . '">BeRocket Account settings</a>
                                    ',
                                'righthtml'     => '',
                                'rightwidth'    => 0,
                                'nothankswidth' => 0,
                                'contentwidth'  => 1600,
                                'subscribe'     => false,
                                'priority'      => 10,
                                'height'        => 50,
                                'repeat'        => false,
                                'repeatcount'   => 1,
                                'image'         => array(
                                    'local'  => '',
                                    'width'  => 0,
                                    'height' => 0,
                                    'scale'  => 1,
                                )
                            );
                        }
                    }
                }

                if ( is_network_admin() ) {
                    set_site_transient( 'berocket_not_activated_notices_site', $not_activated_notices, 7200 );
                } else {
                    set_transient( 'berocket_not_activated_notices', $not_activated_notices, 7200 );
                }
            }

            $notices = array_merge( $notices, $not_activated_notices );

            return $notices;
        }

        public static function get_plugin_count() {
            $count = count( self::$plugin_info );

            return $count;
        }

        public static function allow_berocket_host( $allow, $host, $url ) {
            if ( $host == 'berocket.com' ) {
                $allow = true;
            }

            return $allow;
        }

        public static function test_key() {
            if ( ! isset( $_POST[ 'key' ] ) || ! isset( $_POST[ 'id' ] ) ) {
                $data = array(
                    'key_exist' => 0,
                    'status'    => 'Failed',
                    'error'     => 'Incorrect query for this function(ID and Key must be sended)'
                );

                $out  = json_encode( $data );
            } else {
                $key = sanitize_text_field( $_POST[ 'key' ] );
                $id  = sanitize_text_field( $_POST[ 'id' ] );
                $out = get_transient( 'brupdate_' . $id . '_' . $key );

                if ( $out == false ) {
                    $site_url = get_site_url();
                    $plugins  = self::$plugin_info;

                    if ( is_array( $plugins ) ) {
                        $plugins = array_keys( $plugins );
                        $plugins = implode( ',', $plugins );
                    } else {
                        $plugins = '';
                    }

                    $response = wp_remote_post( BeRocket_update_path . 'main/account_updater', array(
                        'body'        => array(
                            'key'     => $key,
                            'id'      => $id,
                            'url'     => $site_url,
                            'plugins' => $plugins
                        ),
                        'method'      => 'POST',
                        'timeout'     => 30,
                        'redirection' => 5,
                        'blocking'    => true,
                        'sslverify'   => false
                    ) );

                    if ( ! is_wp_error( $response ) ) {
                        $out            = wp_remote_retrieve_body( $response );
                        $current_plugin = false;
                        $out            = json_decode( $out, true );

                        if ( ! is_array( $out ) ) {
                            $out = array();
                        }

                        $out[ 'upgrade' ] = array();
                        $options          = self::get_options();

                        if ( $id != 0 ) {
                            foreach ( self::$plugin_info as $plugin ) {
                                if ( $plugin[ 'id' ] == $id ) {
                                    $current_plugin = $plugin;
                                    break;
                                }
                            }

                            if ( $current_plugin !== false ) {
                                if ( empty( $out[ 'error' ] ) ) {
                                    $options[ 'plugin_key' ][ $id ] = $key;

                                    if ( isset( $out[ 'versions' ][ $id ] ) && version_compare( $current_plugin[ 'version' ], $out[ 'versions' ][ $id ], '<' ) ) {
                                        $upgrade_button        = '<a href="' . wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $current_plugin[ 'plugin' ], 'upgrade-plugin_' . $current_plugin[ 'plugin' ] ) . '" class="button-primary button">Upgrade plugin</a>';
                                        $out[ 'plugin_table' ] = '<p>' . $upgrade_button . '</p>' . $out[ 'plugin_table' ];
                                        $out[ 'upgrade' ][]    = array( 'id' => $id, 'upgrade' => $upgrade_button );
                                    }
                                }
                            }
                        } else {
                            foreach ( self::$plugin_info as $plugin ) {
                                if ( isset( $out[ 'versions' ][ $plugin[ 'id' ] ] ) && version_compare( $plugin[ 'version' ], $out[ 'versions' ][ $plugin[ 'id' ] ], '<' ) ) {
                                    $upgrade_button     = '<a href="' . wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $plugin[ 'plugin' ], 'upgrade-plugin_' . $plugin[ 'plugin' ] ) . '" class="button-primary button">Upgrade plugin</a>';
                                    $out[ 'upgrade' ][] = array(
                                        'id'      => $plugin[ 'id' ],
                                        'upgrade' => $upgrade_button
                                    );
                                }
                            }
                            $options[ 'account_key' ] = $key;
                        }

                        self::set_options( $options );

                        delete_site_transient( 'update_plugins' );
                        $out = json_encode( $out );
                        set_transient( 'brupdate_' . $id . '_' . $key, $out, 600 );
                    } else {
                        $data = array(
                            'key_exist' => 0,
                            'status'    => 'Failed',
                            'error'     => $response->get_error_message()
                        );

                        $out  = json_encode( $data );
                    }
                }
                self::update_check_set('');
            }
            echo $out;
            wp_die();
        }

        public static function scripts() {
            ?>
            <script>
                function BeRocket_key_check(key, show_correct, product_id) {
                    if (typeof( product_id ) == 'undefined' || product_id == null) {
                        product_id = 0;
                    }
                    data = {action: 'br_test_key', key: key, id: product_id};
                    is_submit = false;
                    jQuery.ajax({
                        url: ajaxurl,
                        data: data,
                        type: 'POST',
                        success: function (data) {
                            jQuery('.berocket_test_result').html(data);
                            if (data.key_exist == 1) {
                                if (show_correct) {
                                    html = '<h3>' + data.status + '</h3>';
                                    html += '<p><b>UserName: </b>' + data.username + '</p>';
                                    html += '<p><b>E-Mail: </b>' + data.email + '</p>';
                                    html += data.plugin_table;
                                    jQuery('.berocket_test_result').html(html);
                                    data.upgrade.forEach(function (el, i, arr) {
                                        jQuery('.berocket_product_key_' + el.id + '_status').html(el.upgrade);
                                    });
                                }
                                is_submit = true;
                            } else {
                                html = '<h3>' + data.status + '</h3>';
                                html += '<p><b>Error message:</b>' + data.error + '</p>';
                                jQuery('.berocket_test_result').html(html);
                            }
                            jQuery('.berocket_product_key_' + product_id + '_status').text(data.status);
                        },
                        dataType: 'json',
                        async: false
                    });
                    return is_submit;
                }
                jQuery(document).on('click', '.berocket_test_account_product', function (event) {
                    event.preventDefault();
                    if (jQuery(this).data('product')) {
                        key = jQuery(jQuery(this).data('product')).val();
                    } else {
                        key = jQuery('#berocket_product_key').val();
                    }
                    BeRocket_key_check(key, true, jQuery(this).data('id'));
                });
            </script>
            <style>
                .toplevel_page_berocket_account .dashicons-before img {
                    max-width: 16px;
                }
            </style>
            <?php
        }

        public static function network_account_page() {
            add_menu_page( 'BeRocket Account Settings', 'BeRocket Account', 'manage_options', 'berocket_account', array(
                    __CLASS__,
                    'account_form_network'
                ), plugin_dir_url( __FILE__ ) . 'ico.png', '55.55' );
        }

        public static function main_menu_item() {
            add_menu_page( 'BeRocket Account', 'BeRocket', 'manage_woocommerce', 'berocket_account', array(
                    __CLASS__,
                    'account_form'
                ), plugin_dir_url( __FILE__ ) . 'ico.png', '55.55' );
        }

        public static function account_page() {
            add_submenu_page( 'berocket_account', 'BeRocket Account Settings', 'Account Keys', 'manage_options', 'berocket_account', array(
                    __CLASS__,
                    'account_form'
                ) );
        }

        public static function account_option_register() {
            register_setting( 'BeRocket_account_option_settings', 'BeRocket_account_option' );
        }

        public static function account_form() {
            ?>
            <div class="wrap">
                <form method="post" action="options.php" class="account_key_send">
                    <?php
                    $options = get_option( 'BeRocket_account_option' );
                    self::inside_form( $options );
                    ?>
                </form>
            </div>
            <?php
        }

        public static function account_form_network() {
            ?>
            <div class="wrap">
                <form method="post" action="edit.php?page=berocket_account" class="account_key_send">
                    <?php
                    if ( isset( $_POST[ 'BeRocket_account_option' ] ) ) {
                        $option = berocket_sanitize_array( $_POST[ 'BeRocket_account_option' ] );
                        update_site_option( 'BeRocket_account_option', $option );
                    }

                    $options = get_site_option( 'BeRocket_account_option' );
                    self::inside_form( $options );
                    ?>
                </form>
            </div>
            <?php
        }

        public static function inside_form( $options ) {
            settings_fields( 'BeRocket_account_option_settings' );
            if ( isset( $options[ 'plugin_key' ] ) && is_array( $options[ 'plugin_key' ] ) ) {
                $plugins_key = $options[ 'plugin_key' ];
            } else {
                $plugins_key = array();
            }
            ?>
            <h2>BeRocket Account Settings</h2>
            <div>
                <table>
                    <tr>
                        <td><h3>DEBUG MODE</h3></td>
                        <td colspan=3><label><input type="checkbox" name="BeRocket_account_option[debug_mode]"
                                                    value="1"<?php if ( ! empty( $options[ 'debug_mode' ] ) )
                                    echo ' checked' ?>>Enable debug mode</label></td>
                    </tr>
                    <tr>
                        <td><h3>Account key</h3></td>
                        <td><input type="text" id="berocket_account_key" name="BeRocket_account_option[account_key]"
                                   size="50"
                                   value="<?php echo( empty( $options[ 'account_key' ] ) ? '' : $options[ 'account_key' ] ) ?>">
                        </td>
                        <td><input class="berocket_test_account button-secondary" type="button" value="Test"></td>
                        <td class="berocket_product_key_0_status"></td>
                    </tr>
                    <?php
                    foreach ( self::$plugin_info as $plugin ) {
                        echo '<tr class="berocket_updater_plugin_key" data-id="', $plugin[ 'id' ], '">';
                        echo '<td><h4>';
                        if ( isset( $plugin[ 'name' ] ) ) {
                            echo $plugin[ 'name' ];
                        } else {
                            echo $plugin[ 'slug' ];
                        }
                        echo '</h4></td>';
                        echo '<td><input id="berocket_product_key_', $plugin[ 'id' ], '" size="50" name="BeRocket_account_option[plugin_key][', $plugin[ 'id' ], ']" type="text" value="', ( empty( $options[ 'plugin_key' ][ $plugin[ 'id' ] ] ) ? '' : $options[ 'plugin_key' ][ $plugin[ 'id' ] ] ), '"></td>';
                        echo '<td><input class="berocket_test_account_product button-secondary" data-id="', $plugin[ 'id' ], '" data-product="#berocket_product_key_', $plugin[ 'id' ], '" type="button" value="Test"></td>';
                        echo '<td class="berocket_product_key_status berocket_product_key_', $plugin[ 'id' ], '_status"></td>';
                        echo '</tr>';
                        unset( $plugins_key[ $plugin[ 'id' ] ] );
                    }
                    foreach ( $plugins_key as $key_id => $key_val ) {
                        echo '<input name="BeRocket_account_option[plugin_key][', $key_id, ']" type="hidden" value="', $key_val, '">';
                    }
                    ?>
                </table>
            </div>
            <div class="berocket_test_result"></div>
            <input type="submit" class="button-primary" value="Save Changes"/>

            <div class="berocket_debug_errors">
                <h3>Errors</h3>
                <div>
                    Select plugin
                    <select class="berocket_select_plugin_for_error">
                        <?php
                        foreach ( self::$plugin_info as $plugin ) {
                            echo '<option value="' . $plugin[ 'id' ] . '">' . $plugin[ 'name' ] . '</option>';
                        }
                        ?>
                    </select>
                    <button type="button" class="button berocket_get_plugin_for_error">Get errors</button>
                    <div class="berocket_html_plugin_for_error"></div>
                </div>
            </div>
            <script>
                jQuery('.berocket_get_plugin_for_error').click(function () {
                    var plugin_id = jQuery('.berocket_select_plugin_for_error').val();
                    jQuery.post(ajaxurl, {action: 'berocket_error_notices_get', plugin_id: plugin_id}, function (data) {
                        jQuery('.berocket_html_plugin_for_error').html(data);
                    });
                });
                jQuery('.berocket_test_account').click(function (event) {
                    event.preventDefault();
                    key = jQuery('#berocket_account_key').val();
                    BeRocket_key_check(key, true);
                });
                jQuery(document).on('submit', '.account_key_send', function (event) {
                    key = jQuery('#berocket_account_key').val();
                    if (key != '') {
                        result = BeRocket_key_check(key, false);
                        if (!result) {
                            event.preventDefault();
                        }
                    }
                });
            </script>
            <?php
        }

        public static function update_check_set( $value ) {
            if ( is_network_admin() ) {
                $active_plugin = get_site_option( 'berocket_key_activated_plugins' );
            } else {
                $active_plugin = get_option( 'berocket_key_activated_plugins' );
            }

            $no_update_paid = array();

            foreach ( self::$plugin_info as $plugin ) {
                if ( ! empty( self::$key ) && strlen( self::$key ) == 40 ) {
                    $key = self::$key;
                }

                if ( ! empty( $plugin[ 'key' ] ) && strlen( $plugin[ 'key' ] ) == 40 ) {
                    $key = $plugin[ 'key' ];
                }

                $version = false;
                if ( ! empty( $key ) ) {
                    $version = get_transient( 'brversion_' . $plugin[ 'id' ] . '_' . $key );
                    if ( $version == false ) {
                        $site_url = get_site_url();
                        $url      = BeRocket_update_path . 'main/get_plugin_version/' . $plugin[ 'id' ] . '/' . $key;

                        $response = wp_remote_post( $url, array(
                            'body'        => array(
                                'url' => $site_url
                            ),
                            'method'      => 'POST',
                            'timeout'     => 30,
                            'redirection' => 5,
                            'blocking'    => true,
                            'sslverify'   => false
                        ) );

                        if ( ! is_wp_error( $response ) ) {
                            $out = wp_remote_retrieve_body( $response );
                            if ( ! empty( $out ) ) {
                                $out = json_decode( @ $out );
                                if ( ! empty( $out->status ) && $out->status == 'success' ) {
                                    $version = $out->version;
                                }
                            }
                        }
                        set_transient( 'brversion_' . $plugin[ 'id' ] . '_' . $key, $version, 600 );
                    }
                }

                if ( ! is_array( $active_plugin ) ) {
                    $active_plugin = array();
                }

                $responsed = false;
                if ( $version !== false ) {
                    $active_plugin[ $plugin[ 'id' ] ] = true;
                    if ( version_compare( $plugin[ 'version' ], $version, '<' ) && ! empty($value) ) {
                        $value->checked[ $plugin[ 'plugin' ] ]  = $version;
                        $val                                    = (object) array(
                            'id'          => 'br_' . $plugin[ 'id' ],
                            'new_version' => $version,
                            'package'     => BeRocket_update_path . 'main/update_product/' . $plugin[ 'id' ] . '/' . $key,
                            'url'         => BeRocket_update_path . 'product/' . $plugin[ 'id' ],
                            'plugin'      => $plugin[ 'plugin' ],
                            'slug'        => $plugin[ 'slug' ]
                        );
                        $value->response[ $plugin[ 'plugin' ] ] = $val;
                        $responsed = true;
                    }
                } else {
                    $active_plugin[ $plugin[ 'id' ] ] = false;
                }
                if( ! $responsed && isset($plugin[ 'version_capability' ]) && $plugin[ 'version_capability' ] >= 10 ) {
                    $val                                    = (object) array(
                        'id'          => 'br_' . $plugin[ 'id' ],
                        'new_version' => $plugin[ 'version' ],
                        'package'     => BeRocket_update_path . 'main/update_product/' . $plugin[ 'id' ] . '/' . ( empty($key) ? 'none' : $key ),
                        'url'         => BeRocket_update_path . 'product/' . $plugin[ 'id' ],
                        'plugin'      => $plugin[ 'plugin' ],
                        'slug'        => $plugin[ 'slug' ]
                    );
                    $no_update_paid[$plugin[ 'plugin' ]] = $val;
                }
            }

            if ( is_network_admin() ) {
                update_site_option( 'berocket_key_activated_plugins', $active_plugin );
            } else {
                update_option( 'berocket_key_activated_plugins', $active_plugin );
            }

            delete_site_transient( 'berocket_not_activated_notices_site' );
            delete_transient( 'berocket_not_activated_notices' );
            if ( is_multisite() ) {  
                global $wpdb;  

                $current_site = get_current_site();
                $all_sites = get_sites(array('fields' => 'ids'));
                if( is_array($all_sites) ) {
                    foreach($all_sites as $site_id) {
                        switch_to_blog($site_id);
                        delete_site_transient( 'berocket_not_activated_notices_site' );
                        delete_transient( 'berocket_not_activated_notices' );
                        restore_current_blog();
                    }
                }
            }
            if ( ! empty($value) && isset( $value->no_update ) && is_array( $value->no_update ) ) {
                $value->no_update = array_merge($value->no_update, $no_update_paid);
                foreach ( $value->no_update as $key => $val ) {
                    if ( isset( $val->slug ) && in_array( $val->slug, self::$slugs ) ) {
                        if( ! array_key_exists($key, $no_update_paid) ) {
                            unset( $value->no_update[ $key ] );
                        }
                    }
                }
            }

            return $value;
        }

        public static function plugin_info() {
            $plugin = wp_unslash( $_REQUEST[ 'plugin' ] );

            if ( in_array( $plugin, self::$slugs ) ) {
                remove_action( 'install_plugins_pre_plugin-information', 'install_plugin_information' );

                $plugin_id   = array_search( $plugin, self::$slugs );
                $plugin_info = get_transient( 'brplugin_info_' . $plugin_id );

                if ( $plugin_info == false ) {
                    $url      = BeRocket_update_path . 'main/update_info/' . $plugin_id;
                    $site_url = get_site_url();
                    $response = wp_remote_post( $url, array(
                        'body'        => array(
                            'url' => $site_url
                        ),
                        'method'      => 'POST',
                        'timeout'     => 30,
                        'redirection' => 5,
                        'blocking'    => true,
                        'sslverify'   => false
                    ) );

                    if ( ! is_wp_error( $response ) ) {
                        $plugin_info = wp_remote_retrieve_body( $response );
                        set_transient( 'brplugin_info_' . $plugin_id, $plugin_info, 600 );
                    }
                }

                echo $plugin_info;
                die;
            }
        }

        public static function get_options() {
            if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
                require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
            }

            if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
                $options = get_site_option( 'BeRocket_account_option' );
            } else {
                $options = get_option( 'BeRocket_account_option' );
            }

            return $options;
        }

        public static function set_options( $options ) {
            if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
                require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
            }

            if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
                update_site_option( 'BeRocket_account_option', $options );
            } else {
                update_option( 'BeRocket_account_option', $options );
            }
            self::update_check_set('');
        }
        public static function admin_notice_is_display_notice($display_notice, $item, $search_data) {
            if( ! empty($item['for_plugin']) && is_array($item['for_plugin']) && ! empty($item['for_plugin']['id']) && ! empty($item['for_plugin']['version']) ) {
                $has_free = false;
                foreach ( self::$plugin_info as $plugin ) {
                    if( version_compare($plugin[ 'version' ], '2.0', '<') ) {
                        $has_free = true;
                    }
                    if ( $plugin[ 'id' ] == $item['for_plugin']['id'] && version_compare($plugin[ 'version' ], $item['for_plugin']['version'], '>=') ) {
                        $display_notice = false;
                        break;
                    }
                }
                if( ! $has_free && ! empty($item['for_plugin']['onlyfree']) ) {
                    $display_notice = false;
                }
            }
            return $display_notice;
        }
    }

    BeRocket_updater::init();
    add_action( 'plugins_loaded', array( 'BeRocket_updater', 'run' ), 999 );
}
