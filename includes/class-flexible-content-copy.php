<?php

require_once 'class-flexible-content-copy-ajax.php';

/**
 * Plugin Class
 *
 * @class Class Flexible_Content_Copy
 * @package ACF Flexible Content Copy
 */
class Flexible_Content_Copy {

	/**
	 * @var string
	 */
	public $plugin_version = '1.0.0';

	public function __construct() {
		$this->admin_hooks();
		$this->admin_ajax();
	}

	/**
	 * Register hooks
	 */
	private function admin_hooks() {
		add_action( 'post_submitbox_start', array( $this, 'render_button' ), 9 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	private function admin_ajax() {
		$ajax_ctrl = new Flexible_Content_Copy_Ajax();
		add_action( 'wp_ajax_flexible-content-copy/load-posts', array( $ajax_ctrl, 'posts' ) );
		add_action( 'wp_ajax_flexible-content-copy/layouts', array( $ajax_ctrl, 'layouts' ) );
		add_action( 'wp_ajax_flexible-content-copy/save', array( $ajax_ctrl, 'post_save' ) );
	}

	/**
	 * Template
	 */
	public function render_button() {
		require 'views/post-edit.php';
	}

	/**
	 * Register script and styles
	 */
	public function enqueue_scripts() {
		$plugin_url = plugin_dir_url( __FILE__ ) . '../';

		wp_enqueue_script( 'flexible-content-copy', $plugin_url . 'assets/js/flexible-content-copy.min.js', array(
			'jquery',
			'backbone',
			'underscore',
			'thickbox',
		), $this->plugin_version, true );

		wp_localize_script( 'flexible-content-copy', 'FlexibleContentCopyLocalize', array(
			'loader' => esc_url( admin_url( 'images/spinner.gif' ) ),
			'url'    => esc_url( admin_url( 'admin-ajax.php' ) )
		) );

		wp_enqueue_style( 'flexible-content-copy', $plugin_url . 'assets/css/flexible-content-copy.min.css', array(), $this->plugin_version );
	}

}