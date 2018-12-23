<?php

global $pagenow, $post;

$BeRocket_advanced_labels_custom_post = BeRocket_advanced_labels_custom_post::getInstance();

$label = [
	'label_from_post' => '',
];

if (!in_array($pagenow, ['post-new.php'], true)) {
	$label = $BeRocket_advanced_labels_custom_post->get_option($post->ID);
}

echo '<div class="panel wc-metaboxes-wrapper" id="br_alabel" style="display: none;">';
wp_nonce_field('br_labels_check', 'br_labels_nonce');
echo '<table><tr><th>' . __('Label to display on this product', 'BeRocket_products_label_domain') . '</th>
<td><div style="max-height:200px;margin:10px 0;overflow: auto;">';

$args = [
	'posts_per_page'   => -1,
	'offset'           => 0,
	'category'         => '',
	'category_name'    => '',
	'orderby'          => 'date',
	'order'            => 'DESC',
	'include'          => '',
	'exclude'          => '',
	'meta_key'         => '',
	'meta_value'       => '',
	'post_type'        => 'br_labels',
	'post_mime_type'   => '',
	'post_parent'      => '',
	'author'           => '',
	'post_status'      => 'publish',
	'suppress_filters' => false,
];
$posts_array = get_posts($args);
foreach ($posts_array as $post_id) {
	$post_title = get_the_title($post_id->ID);
	echo '<p><label><input name="br_labels[label_from_post][]" type="checkbox" value="' . $post_id->ID . '"' . (is_array($label['label_from_post']) && in_array($post_id->ID, $label['label_from_post'], true) ? ' checked' : '') . '>(' . $post_id->ID . ') ' . $post_title . '</label></p>';
}
echo '</div></td></tr></table>';
?>
<div class="berocket_label_preview_wrap">
	<div class="berocket_label_preview">
		<img class="berocket_product_image" src="<?php echo plugin_dir_url(__FILE__) . '../images/labels.png'; ?>">
	</div>
</div>
<?php
$BeRocket_advanced_labels_custom_post->settings($post);
?>
</div>
<style>
	.berocket_label_preview_wrap {
		display: inline-block;
		width: 240px;
		padding: 20px;
		background: white;
		position: fixed;
		top: 100%;
		margin-top: -320px;
		min-height: 320px;
		right: 20px;
		box-sizing: border-box;
	}
	.berocket_label_preview_wrap .berocket_label_preview {
		position: relative;
	}
	.berocket_label_preview_wrap .berocket_product_image {
		display: block;
		width: 200px;
	}
	@media screen and (max-width: 850px) {
		.berocket_label_preview_wrap {
			position: relative;
		}
	}
</style>
