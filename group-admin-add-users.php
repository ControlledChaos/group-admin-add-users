<?php

/**
 * Plugin Name: BuddyPress Group Admin Add Users
 * Plugin URI: 
 * Version: 1.0.0
 * Author: BuddyDev Team
 * Author URI: http://buddydev.com
 * Description: This plugin allow group's admin to create users
 *
 * License: GPL2 or Above
 *
 */


class Group_Admin_Add_Users {

	private $path;

	public function __construct() {

		$this->path = plugin_dir_path( __FILE__ );
		$this->setup();

	}

	public function setup() {
		add_action( 'bp_loaded', array( $this, 'load' ) );
	}

	public function load() {
		require_once $this->path . 'ga-group-extension.php';
	}

}
new Group_Admin_Add_Users();