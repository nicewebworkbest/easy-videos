<?php
/*
 * Plugin Name: Easy Videos
 * Plugin URI: https://easy-vides.com
 * Description: Import videos from a Youtube video channel to video custom post type.
 * Version: 1.0.0
 * Author: Sergei
 * License: GPLv2 or later
 * Text Domain: easy-videos
 * Domain Path: /languages/
 */

define( 'EASY_VIDEOS_VERSION', '1.0.0' );
define( 'EASY_VIDEOS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EASY_VIDEOS_PLUGIN_URI', plugin_dir_url( __FILE__ ) );

require_once( EASY_VIDEOS_PLUGIN_DIR . 'class.easy-videos.php' );
require_once( EASY_VIDEOS_PLUGIN_DIR . 'class.easy-videos.php' );

$easyVideos = new EasyVideos();
$easyVideos->init();
