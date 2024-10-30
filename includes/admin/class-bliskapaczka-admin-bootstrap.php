<?php
/**
 * Bliskapaczka.pl Admin Bootstrap
 *
 * @author   Bliskapaczka.pl
 * @category Admin
 * @package  Bliskapaczka/Admin/Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 *  Bliskapacza boot in admin
 */
class Bliskapaczka_Admin_Bootstrap {


	/**
	 * Instance of this class
	 *
	 * @var Bliskapaczka_Admin_Bootstrap
	 */
	private static $instance;

	/**
	 * Constructor.
	 */
	private function __construct() {
	}

	/**
	 * Singleton
	 *
	 * @return Bliskapaczka_Admin_Bootstrap
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Bliskapaczka_Admin_Bootstrap();
		}

		return self::$instance;
	}

	/**
	 * Initialize all admin operations
	 *
	 * @return Bliskapaczka_Admin_Bootstrap
	 */
	public function boot() {
		if ( $this->is_admin() ) {
			$this->registry();
		}

		return $this;
	}
	
	/**
	 * Registry admin operations
	 */
	private function registry() 
	{
		$this
			->registry_actions()
			->registry_filters()
			->registry_pages();
		
		Bliskapaczka_Admin_Component::registry();
		Bliskapaczka_Admin_Shipping_Documents::registry();
		
		add_action( 'admin_enqueue_scripts', [ 'Bliskapaczka_Admin_Bootstrap', 'registry_styles_and_scripts' ] );
	}

	/**
	 * Registers actions in administration
	 *
	 * @return Bliskapaczka_Admin_Bootstrap
	 */
	private function registry_actions() 
	{
		add_action('admin_footer', [ 'Bliskapaczka_Admin_Bootstrap', 'print_modal' ]);
		
		return $this;
	}

	/**
	 * Registers filters in administration
	 *
	 * @return Bliskapaczka_Admin_Bootstrap
	 */
	private function registry_filters() 
	{
		$orderDetails = new Bliskapaczka_Admin_Order_Details();

		add_filter( 'woocommerce_admin_order_data_after_shipping_address', [ $orderDetails, 'shipping_details' ], 1 );
		add_filter( 'woocommerce_admin_order_data_after_shipping_address', [ $orderDetails, 'shipping_show_msg_warn' ], 2 );

		return $this;
	}

	/**
	 * Registry pages
	 */
	private function registry_pages() 
	{
		return $this;
	}

	/**
	 * Registry styles and scripts
	 */
	public static function registry_styles_and_scripts( $hook ) 
	{
		$plugin_dir_url = plugin_dir_url( dirname(dirname(__FILE__)) );
		
		wp_register_style( 'bliskapaczka_admin_styles',  $plugin_dir_url . 'assets/css/bliskapaczka_admin.css', array(), 'v1', false );
		wp_enqueue_style( 'bliskapaczka_admin_styles' );
		
		wp_register_script( 'bliskapaczka-admin-scripts-library', $plugin_dir_url . 'assets/js/bliskapaczka_admin.lib.js', array(), 'v' . time(), true );
		wp_enqueue_script( 'bliskapaczka-admin-scripts-library' );
		
		wp_register_script( 'bliskapaczka-admin-scripts-initialize', $plugin_dir_url . 'assets/js/bliskapaczka_admin.init.js', array(), 'v' . time(), true );
		wp_enqueue_script( 'bliskapaczka-admin-scripts-initialize' );
		
		add_thickbox();
	}
	
	/**
	 * Registry modal in admin
	 */
	public static function print_modal()
	{
		global $current_screen;
		
		// Not our post type, exit earlier
		if( 'shop_order' !== $current_screen->post_type )
		{
			return;
		}
		
		echo '<div id="bliskapaczka-modal" style="display:none;">
			<div id="bliskapaczka-modal-body"></div>
		</div>';
	}
	
	/**
	 * Return true if the request is from Admin Panel.
	 *
	 * @return boolean
	 */
	private function is_admin() 
	{
		return is_admin();
	}
}
