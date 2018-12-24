<?php

class berocket_admin_notices {

	public $find_names;
	public $notice_exist = false;
	public static $last_time = '-24 hours';
	public static $end_soon_time = '+1 hour';
	public static $jquery_script_exist = false;
	public static $styles_exist = false;
	public static $notice_index = 0;

	public static $default_notice_options = [
		'start'         => 0,
		'end'           => 0,
		'name'          => 'sale',
		'html'          => '',
		'righthtml'     => '<a class="berocket_no_thanks">No thanks</a>',
		'rightwidth'    => 80,
		'nothankswidth' => 60,
		'contentwidth'  => 400,
		'subscribe'     => false,
		'closed'        => '0',
		'priority'      => 20,
		'height'        => 50,
		'repeat'        => false,
		'repeatcount'   => 1,
		'image'         => [],
	];

	public function __construct($options = []) {
		if (is_admin()) {
			$options = array_merge(self::$default_notice_options, $options);
			self::set_notice_by_path($options);
		}
	}

	public static function sort_notices($notices) {
		return self::sort_array(
			$notices,
			[
				1 => 'krsort',
				2 => 'ksort',
				3 => 'ksort',
			],
			[
				'1' => SORT_NUMERIC,
				'2' => SORT_NUMERIC,
				'3' => SORT_NUMERIC,
			]
		);
	}

	public static function sort_array($array, $sort_functions, $options, $count = 3) {
		if ($count > 0) {
			if (! is_array($array)) {
				return [];
			}
			$call_function = $sort_functions[$count];
			$call_function($array, $options[$count]);
			if (isset($array[0])) {
				$first_element = $array[0];
				unset($array[0]);
				$array[0] = $first_element;
				unset($first_element);
			}
			foreach ($array as $item_id => $item) {
				if ($count == 2) {
					$time = time();
					if ($item_id < $time && $item_id != 0) {
						unset($array[$item_id]);
					} else {
						$array[$item_id] = self::sort_array($item, $sort_functions, $options, $count - 1);
					}
				} else {
					$array[$item_id] = self::sort_array($item, $sort_functions, $options, $count - 1);
				}
				if (isset($array[$item_id]) && (! is_array($array[$item_id]) || count($array[$item_id]) == 0)) {
					unset($array[$item_id]);
				}
			}
		}
		return $array;
	}
	public static function get_notice_by_path($find_names) {
		$notices = get_option('berocket_admin_notices');
		if (!is_array($notices)) {
			$notices = [];
		}

		$current_notice = &$notices;
		foreach ($find_names as $find_name) {
			if (isset($current_notice[$find_name])) {
				$new_current_notice = &$current_notice[$find_name];
				unset($current_notice);
				$current_notice = &$new_current_notice;
				unset($new_current_notice);
			} else {
				unset($current_notice);
				break;
			}
		}

		if (! isset($current_notice)) {
			$current_notice = false;
		}

		return $current_notice;
	}

	public static function berocket_array_udiff_assoc_notice($a1, $a2) {
		return json_encode($a1) > json_encode($a2);
	}

	public static function set_notice_by_path($options, $replace = false, $find_names = false) {
		$notices = get_option('berocket_admin_notices');
		if ($options['end'] < time() && $options['end'] != 0) {
			return false;
		}
		if ($find_names === false) {
			$find_names = [$options['priority'], $options['end'], $options['start'], $options['name']];
		}
		if (! is_array($notices)) {
			$notices = [];
		}

		$current_notice = &$notices;
		foreach ($find_names as $find_name) {
			if (! isset($current_notice[$find_name])) {
				$current_notice[$find_name] = [];
			}
			$new_current_notice = &$current_notice[$find_name];
			unset($current_notice);
			$current_notice = &$new_current_notice;
			unset($new_current_notice);
		}
		$array_diff = array_udiff_assoc($options, $current_notice, [__CLASS__, 'berocket_array_udiff_assoc_notice']);
		if (isset($array_diff['image'])) {
			unset($array_diff['image']);
		}

		if (count($array_diff) == 0) {
			return true;
		}
		if (empty($options['image']) || (empty($options['image']['local']) && empty($options['image']['global']))) {
			$options['image'] = ['width' => 0, 'height' => 0, 'scale' => 0];
		} else {
			$file_exist = false;
			if (isset($options['image']['global'])) {
				$wp_upload = wp_upload_dir();
				if (! isset($options['image']['local'])) {
					$url_global = $options['image']['global'];
					$img_local = $wp_upload['basedir'] . '/' . basename($url_global);
					$url_local = $wp_upload['baseurl'] . '/' . basename($url_global);
					if (! file_exists($img_local) && is_writable($wp_upload['path'])) {
						file_put_contents($img_local, file_get_contents($url_global));
					}
					if (file_exists($img_local)) {
						$options['image']['local'] = $url_local;
						$options['image']['pathlocal'] = $img_local;
					} else {
						$options['image']['local'] = $url_global;
						$file_exist = true;
					}
				}
			}
			if (! $file_exist) {
				if (! empty($options['image']['local'])) {
					$img_local = $options['image']['local'];
					$img_local = str_replace(site_url('/'), '', $img_local);
					$img_local = ABSPATH . $img_local;
					$file_exist = (file_exists($img_local));
				} else {
					$file_exist = false;
				}
			}
			if ($file_exist) {
				$check_size = true;
				if (isset($current_notice['image']['local']) && $current_notice['image']['local'] == $options['image']['local']) {
					if (isset($current_notice['image']['width'], $current_notice['image']['height'])) {
						$options['image']['width'] = $current_notice['image']['width'];
						$options['image']['height'] = $current_notice['image']['height'];
						$check_size = false;
					}
				}
				if ($check_size) {
					$image_size = @ getimagesize($options['image']['local']);
					if (! empty($image_size[0]) && ! empty($image_size[1])) {
						$options['image']['width'] = $image_size[0];
						$options['image']['height'] = $image_size[1];
					} else {
						$options['image']['width'] = $options['height'];
						$options['image']['height'] = $options['height'];
					}
				}
				$options['image']['scale'] = $options['height'] / $options['image']['height'];
			} else {
				$options['image'] = ['width' => 0, 'height' => 0, 'scale' => 0];
			}
		}
		if (count($current_notice) == 0) {
			$current_notice = $options;
		} else {
			if (! empty($options['image']['local']) && $options['image']['local'] != $current_notice['image']['local']) {
				if (isset($current_notice['image']['pathlocal'])) {
					unlink($current_notice['image']['pathlocal']);
				}
			}
			if (! $replace) {
				$options['closed'] = $current_notice['closed'];
			}
			$current_notice = $options;
		}
		$notices = self::sort_notices($notices);
		update_option('berocket_admin_notices', $notices);
		return true;
	}

	public static function get_notice() {
		$notices = get_option('berocket_admin_notices');
		$last_time = get_option('berocket_last_close_notices_time');
		if (! is_array($notices) || count($notices) == 0) {
			return false;
		}
		if ($last_time > strtotime(self::$last_time)) {
			$current_notice = self::get_not_closed_notice($notices, true);
		} else {
			$current_notice = self::get_not_closed_notice($notices);
		}
		update_option('berocket_current_displayed_notice', $current_notice);
		return $current_notice;
	}

	public static function get_notice_for_settings() {
		$notices = get_option('berocket_admin_notices');
		$last_notice = get_option('berocket_admin_notices_last_on_options');
		$notices = self::get_notices_with_priority($notices);
		if (! is_array($notices) || count($notices) == 0) {
			return false;
		}
		if ($last_notice === false) {
			$last_notice = 0;
		} else {
			$last_notice++;
		}
		if (count($notices) <= $last_notice) {
			$last_notice = 0;
		}
		update_option('berocket_admin_notices_last_on_options', $last_notice);
		return $notices[$last_notice];
	}

	public static function get_not_closed_notice($array, $end_soon = false, $closed = 0, $count = 3) {
		$notice = false;
		if (empty($array) || ! is_array($array)) {
			$array = [];
		}
		$time = time();
		foreach ($array as $item_id => $item) {
			if ($count > 0) {
				if ($count == 2 && $item_id < $time && $item_id != 0 || $count == 1 && $item_id > $time && $item_id != 0) {
					continue;
				}
				if ($count == 2 && $item_id < strtotime(self::$end_soon_time) && $item_id != 0) {
					$notice = self::get_not_closed_notice($item, $end_soon, 1, $count - 1);
				} else {
					if ($end_soon && $count == 2) {
						break;
					}
					$notice = self::get_not_closed_notice($item, $end_soon, $closed, $count - 1);
				}
			} else {
				$display_notice = ($item['closed'] <= $closed && (!$item['subscribe']) && ($item['start'] == 0 || $item['start'] < $time) && ($item['end'] == 0 || $item['end'] > $time));
				$display_notice = apply_filters('berocket_admin_notice_is_display_notice', $display_notice, $item, [
					'end_soon'   => $end_soon,
					'closed'     => $closed,
				]);
				if ($display_notice) {
					return $item;
				}
			}
			if ($notice != false) {
				break;
			}
		}
		return $notice;
	}

	public static function get_notices_with_priority($array, $priority = 19, $count = 3) {
		if (empty($array) || ! is_array($array)) {
			$array = [];
		}
		$time = time();
		$notices = [];
		foreach ($array as $item_id => $item) {
			if ($count > 0) {
				if ($count == 3 && $item_id > $priority || $count == 2 && $item_id < $time && $item_id != 0 || $count == 1 && $item_id > $time && $item_id != 0) {
					continue;
				}
				$notice = self::get_notices_with_priority($item, $priority, $count - 1);
				$notices = array_merge($notices, $notice);
			} else {
				$display_notice = $item['priority'] <= 5 || !$item['closed'];
				$display_notice = apply_filters('berocket_admin_notice_is_display_notice_priority', $display_notice, $item, [
					'priority'   => $priority,
				]);
				if ($display_notice) {
					$notices[] = $item;
				}
			}
		}
		return $notices;
	}

	public static function display_admin_notice() {
		$settings_page = apply_filters('is_berocket_settings_page', false);
		if ($settings_page) {
			$notice = self::get_notice_for_settings();
		} else {
			$notice = self::get_notice();
		}
		if (! empty($notice['original'])) {
			$original_notice = self::get_notice_by_path($notice['original']);
			unset($original_notice['start'], $original_notice['closed'], $original_notice['repeatcount']);
			$notice = array_merge($notice, $original_notice);
		}

		if ($notice !== false) {
			self::echo_notice($notice);
		}
		$additional_notice = apply_filters('berocket_display_additional_notices', []);
		if (is_array($additional_notice) && count($additional_notice) > 0) {
			foreach ($additional_notice as $notice) {
				if (is_array($notice)) {
					self::echo_notice($notice);
				}
			}
		}
	}

	public static function echo_notice($notice) {
		$notice = array_merge(self::$default_notice_options, $notice);
		$settings_page = apply_filters('is_berocket_settings_page', false);
		self::$notice_index++;
		$notice_data = [
			'start'     => $notice['start'],
			'end'       => $notice['end'],
			'name'      => $notice['name'],
			'priority'  => $notice['priority'],
		];
		if ($notice['end'] < strtotime(self::$end_soon_time) && $notice['end'] != 0) {
			$time_left = $notice['end'] - time();
			$time_left_str = "";
			$time = $time_left;
			if ($time >= 3600) {
				$hours = floor($time/3600);
				$time  = $time%3600;
				$time_left_str .= sprintf("%02d", $hours) . ":";
			}
			if ($time >= 60 || $time_left >= 3600) {
				$minutes = floor($time/60);
				$time  = $time%60;
				$time_left_str .= sprintf("%02d", $minutes) . ":";
			}

			$time_left_str .= sprintf("%02d", $time);
			$notice['rightwidth'] += 60;
			$notice['righthtml'] .= '<div class="berocket_time_left_block">Left<br><span class="berocket_time_left" data-time="' . $time_left . '">' . $time_left_str . '</span></div>';
		}
		echo '<div class="notice berocket_admin_notice berocket_admin_notice_', self::$notice_index, '" data-notice=\'', json_encode($notice_data), '\'>',
				(empty($notice['image']['local']) ? '' : '<img class="berocket_notice_img" src="' . $notice['image']['local'] . '">'),
				(empty($notice['righthtml']) ? '' :
				'<div class="berocket_notice_right_content">
					<div class="berocket_notice_content">' . $notice['righthtml'] . '</div>
					<div class="berocket_notice_after_content"></div>
				</div>'),
				'<div class="berocket_notice_content_wrap">
					<div class="berocket_notice_content">', $notice['html'], '</div>
					<div class="berocket_notice_after_content"></div>
				</div></div>';
		if ($settings_page && $notice['priority'] <= 5) {
			$notice['rightwidth'] -= $notice['nothankswidth'];
		}
		echo '<style>
			.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' {
				height: ', $notice['height'], 'px;
				padding: 0;
				min-width: ', max($notice['image']['width'] * $notice['image']['scale'], $notice['rightwidth']), 'px;
				border-left: 0 none;
				border-radius: 3px;
				overflow: hidden;
				box-shadow: 0 0 3px 0 rgba(0, 0, 0, 0.2);
			}
			.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_img {
				height: ', $notice['height'], 'px;
				width: ', ($notice['image']['width'] * $notice['image']['scale']), 'px;
				float: left;
			}
			.berocket_admin_notice .berocket_notice_content_wrap {
				margin-left: ', ($notice['image']['width'] * $notice['image']['scale'] + 5), 'px;
				margin-right: ', ($notice['rightwidth'] <= 20 ? 0 : $notice['rightwidth'] + 15), 'px;
				box-sizing: border-box;
				height: ', $notice['height'], 'px;
				overflow: auto;
				overflow-x: hidden;
				overflow-y: auto;
				font-size: 16px;
				line-height: 1em;
				text-align: center;
			}
			.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_right_content {',
				($notice['rightwidth'] <= 20 ? ' display: none' :
				'height: ' . $notice['height'] . 'px;
				float: right;
				width: ' . $notice['rightwidth'] . 'px;
				-webkit-box-shadow: box-shadow: -1px 0 0 0 rgba(0, 0, 0, 0.1);
				box-shadow: -1px 0 0 0 rgba(0, 0, 0, 0.1);
				padding-left: 10px;'),
			'}
			.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_no_thanks {',
				($settings_page && $notice['priority'] <= 5 ? 'display: none!important;' : 'cursor: pointer;
				color: #0073aa;
				opacity: 0.5;
				display: inline-block;'),
			'}
			@media screen and (min-width: 783px) and (max-width: ', round($notice['image']['width'] * $notice['image']['scale'] + $notice['rightwidth'] + $notice['contentwidth'] + 10 + 200), 'px) {
				div.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_content_wrap {
					font-size: 14px;
				}
				div.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_button {
					padding: 4px 15px;
				}
			}
			@media screen and (max-width: 782px) {
				div.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_content_wrap {
					margin-left: 0;
					margin-right: 0;
					clear: both;
					height: initial;
				}
				div.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_content {
					line-height: 2.5em;
				}
				div.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_content .berocket_button {
					line-height: 1em;
				}
				div.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' {
					height: initial;
					text-align: center;
					padding: 20px;
				}
				.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_img {
					float: none;
					display: inline-block;
				}
				div.berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_notice_right_content {
					display: block;
					float: none;
					clear: both;
					width: 100%;
					-webkit-box-shadow: none;
					box-shadow: none;
					padding: 0;
				}
			}
		</style>
		<script>
			jQuery(document).ready(function() {
				jQuery(document).on("click", ".berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_no_thanks", function(event){
					event.preventDefault();
					var notice = jQuery(this).parents(".berocket_admin_notice.berocket_admin_notice_', self::$notice_index, '").data("notice");
					jQuery.post(ajaxurl, {action:"berocket_admin_close_notice", notice:notice}, function(data){});
					jQuery(this).parents(".berocket_admin_notice.berocket_admin_notice_', self::$notice_index, '").hide();
				});
			});';
		if ($notice['end'] < strtotime(self::$end_soon_time) && $notice['end'] != 0) {
			echo 'setInterval(function(){
				jQuery(".berocket_admin_notice.berocket_admin_notice_', self::$notice_index, ' .berocket_time_left").each(function(i, o) {
					var left_time = jQuery(o).data("time");
					var time = left_time;
					if( time <= 0 ) {
						jQuery(o).parents(".berocket_admin_notice.berocket_admin_notice_', self::$notice_index, '").hide();
					} else {
						time--;
						jQuery(o).data("time", time);
						var str = "";
						if ( time >= 3600 ) {
							hours = Math.floor( time/3600 );
							time  = time%3600;
							str += ("0" + hours).slice(-2) + ":";
						}
						if ( time >= 60 || left_time >= 3600 ) {
							minutes = Math.floor( time/60 );
							time  = time%60;
							str += ("0" + minutes).slice(-2) + ":";
						}
						seconds = time;
						str += ("0" + seconds).slice(-2);
						jQuery(o).html(str);
					}
				});
			}, 1000);';
		}
		echo '</script>';
		self::echo_styles();
	}

	public static function echo_styles() {
		if (!self::$styles_exist) {
			self::$styles_exist = true; ?>
			<style>
			.berocket_admin_notice .berocket_notice_content {
				display: inline-block;
				vertical-align: middle;
				padding: 2px 5px;
				max-width: 99%;
				box-sizing: border-box;
			}
			.berocket_admin_notice .berocket_notice_after_content {
				display: inline-block;
				vertical-align: middle;
				height: 100%;
				width: 0px;
			}
			.berocket_admin_notice .berocket_time_left_block {
				display: inline-block;
				text-align: center;
				vertical-align: middle;
				padding: 0 0 0 10px;
			}
			.berocket_notice_content .berocket_button {
				margin: 0 0 0 10px;
				min-width: 80px;
				padding: 6px 16px;
				vertical-align: baseline;
				color: #fff;
				box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.26);
				text-shadow: none;
				border: 0 none;
				-moz-user-select: none;
				background: #ff5252 none repeat scroll 0 0;
				box-sizing: border-box;
				cursor: pointer;
				font-size: 15px;
				outline: 0 none;
				position: relative;
				text-align: center;
				text-decoration: none;
				transition: box-shadow 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s, background-color 0.4s cubic-bezier(0.25, 0.8, 0.25, 1) 0s;
				white-space: nowrap;
				height: auto;
				display: inline-block;
				font-weight: bold;
				line-height: 120%;
			}
			</style><?php
		}
	}

}

add_action('admin_notices', ['berocket_admin_notices', 'display_admin_notice']);

