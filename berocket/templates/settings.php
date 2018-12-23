<?php
$plugin_info   = get_plugin_data( $this->cc->info[ 'plugin_file' ] );

$dplugin_name  = $this->cc->info['full_name'];
$dplugin_link  = 'https://berocket.com/product/' . $this->cc->values['premium_slug'];
$dplugin_price = $this->cc->info['price'];
$dplugin_desc  = $plugin_info['Description'];
$options       = $this->get_option();

include 'discount.php';
?>
<div class="wrap br_framework_settings br_<?php echo $this->cc->info['plugin_name']?>_settings">
    <div id="icon-themes" class="icon32"></div>
    <h2><?php echo $this->cc->info['full_name'] . ' ' . __( 'Settings', 'BeRocket_domain' )?></h2>
    <?php settings_errors(); ?>
    <?php $this->cc->admin_settings() ?>
</div>

<?php
include 'settings_footer.php';
?>
