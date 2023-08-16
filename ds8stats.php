<?php
/**
 * @package DS8 Stats
 */
/*
Plugin Name: DS8 Stats
Plugin URI: https://deseisaocho.com/
Description: FD <strong>Stats</strong>
Version: 1.0
Author: JLMA
Author URI: https://deseisaocho.com/wordpress-plugins/
License: GPLv2 or later
Text Domain: ds8stats
*/


if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'DS8STATS_VERSION', '2' );
define( 'DS8STATS_MINIMUM_WP_VERSION', '5.0' );
define( 'DS8STATS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, array( 'DS8Stats', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'DS8Stats', 'plugin_deactivation' ) );

require_once DS8STATS_PLUGIN_DIR . '/includes/helpers.php';
require_once( DS8STATS_PLUGIN_DIR . 'class.ds8stats.php' );

add_action( 'init', array( 'DS8Stats', 'init' ) );

if ( is_admin() ) {
	require_once( DS8STATS_PLUGIN_DIR . 'class.ds8stats-admin.php' );
	add_action( 'init', array( 'DS8Stats_Admin', 'init' ) );
}