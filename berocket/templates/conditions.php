<?php
$condition_types = apply_filters($hook_name . '_types', []);
?>
<div id="apl-conditions">
	<div id="br_condition_example" style="display:none;">
		<div class="br_cond_select" data-current="1">
			<span>
				<select class="br_cond_type">
					<?php
					foreach ($condition_types as $type_slug => $type_name) {
						echo '<option value="' . $type_slug . '">' . $type_name . '</option>';
					}
					?>
				</select>
			</span>
			<span class="button berocket_remove_condition"><i class="fa fa-minus"></i></span>
			<div class="br_current_cond"></div>
		</div>
		<span class="button berocket_add_condition"><i class="fa fa-plus"></i></span>
		<span class="button br_remove_group"><i class="fa fa-minus"></i></span>
	</div>
	<div id="condition-types-example" style="display: none;">
		<?php
		foreach ($condition_types as $condition_type_slug => $condition_type_name) {
			$condition_html = apply_filters($hook_name . '_type_' . $condition_type_slug, '', '%name%[%id%][%current_id%]', []);
			if (!empty($condition_html)) {
				echo '<div class="br_cond br_cond_' . $condition_type_slug . '">' .
					$condition_html .
					'<input type="hidden" name="%name%[%id%][%current_id%][type]" value="' . $condition_type_slug . '">' .
					'</div>';
			}
		}
		?>
	</div>
	<div class="br_conditions">
		<?php
		$last_id = 0;
		foreach ($value as $id => $data) {
			$current_id = 1;
			ob_start();
			foreach ($data as $current => $conditions) {
				if ($current > $current_id) {
					$current_id = $current;
				} ?>
				<div class="br_cond_select" data-current="<?php echo $current; ?>">
					<span>
						<select class="br_cond_type">
							<?php
							foreach ($condition_types as $type_slug => $type_name) {
								echo '<option value="' . $type_slug . '"' . selected(isset($conditions['type']) && $conditions['type'] == $type_slug) . '>' . $type_name . '</option>';
							} ?>
						</select>
					</span>
					<span class="button berocket_remove_condition"><i class="fa fa-minus"></i></span>
					<div class="br_current_cond">
					</div>
				<?php
				$condition_html = apply_filters($hook_name . '_type_' . $conditions['type'], '', $option_name . '[' . $id . '][' . $current . ']', $conditions);
				if (! empty($condition_html)) {
					echo '<div class="br_cond br_cond_', $conditions['type'], '">
					', $condition_html, '
					<input type="hidden" name="' . $option_name . '[' . $id . '][' . $current . '][type]" value="', $conditions['type'], '">
					</div>';
				} ?>
				</div>
				<?php
			} ?>
			<span class="button berocket_add_condition"><i class="fa fa-plus"></i></span>
			<span class="button br_remove_group"><i class="fa fa-minus"></i></span>
			<?php
			$html = ob_get_clean();
			echo '<div class="br_html_condition" data-id="' . $id . '" data-current="' . $current_id . '">';
			echo $html;
			echo '</div>';
			if ($id > $last_id) {
				$last_id = $id;
			}
		}
		$last_id++;
		?>
		<span class="button br_add_group"><i class="fa fa-plus"></i></span>
	</div>
	<script>
		var last_id = <?php echo $last_id; ?>;
		var condition_name = '<?php echo $option_name; ?>';
	</script>
</div>
