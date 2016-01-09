<?php
/*
Plugin Name: Flex Content Copy for ACF
Plugin URI: https://github.com/constlab/acf-flexible-content-copy
Description: Allow copy flex content layout from another post
Version: 1.0.0
Author: Const Lab
Author URI: https://constlab.ru
License: MIT
License URI: https://opensource.org/licenses/MIT
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require __DIR__ . '/includes/class-flexible-content-copy.php';

$flexible_content_copy = new Flexible_Content_Copy();