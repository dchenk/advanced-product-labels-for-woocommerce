<?php

class BeRocket_plugin_variations {
	public $version_number = 0;
	public $plugin_name;
	public $values;
	public $info;
	public $defaults;

	public function __construct() {
		add_filter('brfr_plugin_version_capability_' . $this->plugin_name, [$this, 'plugin_version_capability'], $this->version_number, 2);
		add_filter('brfr_plugin_defaults_value_' . $this->plugin_name, [$this, 'default_values'], $this->version_number, 2);
		add_filter('brfr_data_' . $this->plugin_name, [$this, 'settings_page'], $this->version_number);
		add_filter('brfr_tabs_info_' . $this->plugin_name, [$this, 'settings_tabs'], $this->version_number);
	}

	public function plugin_version_capability($plugin_version_capability, $object) {
		$this->info = $object->info;
		$this->values = $object->values;
		return $this->version_number;
	}

	public function default_values($defaults, $object) {
		if (!is_array($this->defaults)) {
			$this->defaults = [];
		}
		if (is_array($defaults)) {
			$defaults = array_merge($this->defaults, $defaults);
		} else {
			$defaults = $this->defaults;
		}
		return $defaults;
	}

	public function settings_page($data) {
		return $data;
	}

	public function settings_tabs($data) {
		return $data;
	}
}
