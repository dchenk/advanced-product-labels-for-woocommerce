<div class="br_settings_footer">
    <?php if ( isset($this->plugin_version_capability) && $this->plugin_version_capability <= 5 ) {
        $text = '<h4>Both <a href="%plugin_link%" target="_blank">Free</a> and <a href="%link%" target="_blank">Paid</a> versions of %plugin_name% developed by <a href="https://berocket.com" target="_blank">BeRocket</a></h4>';
    } else {
        $text = '<h4><a href="%plugin_link%" target="_blank">%plugin_name%</a> developed by <a href="https://berocket.com" target="_blank">BeRocket</a></h4>';
    }

    $text = str_replace( '%link%',         $dplugin_link,                                                       $text );
    $text = str_replace( '%plugin_name%',  (empty($plugin_info['Name']) ? '' : $plugin_info['Name']),           $text );
    $text = str_replace( '%plugin_link%',  (empty($plugin_info['PluginURI']) ? '' : $plugin_info['PluginURI']), $text );
    echo $text;
    ?>
</div>
