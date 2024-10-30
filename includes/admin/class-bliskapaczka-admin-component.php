<?php
/**
 * Bliskapaczka.pl Admin Bootstrap
 *
 * @author   Bliskapaczka.pl
 * @category Admin
 * @package  Bliskapaczka/Admin/Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

/**
 * Bliskapaczka orders imported component.
 *
 * @author marko
 */
class Bliskapaczka_Admin_Component {


	/**
	 * Registry WordPress hooks, filters action
	 */
	public static function registry() {
		add_action( 'admin_menu', [ 'Bliskapaczka_Admin_Component', 'registryMenu' ] );
		add_action( 'admin_enqueue_scripts', [ 'Bliskapaczka_Admin_Component', 'registryScripts' ] );
	}

	/**
	 * Append link in WordPress menu
	 */
	public static function registryMenu() {
		$page_title = __( 'Lista zleceń bliskapaczka.pl', 'bliskapaczka-pl' );
		$menu_title = __( 'Lista zleceń bliskapaczka.pl', 'bliskapaczka-pl' );
		$capability = 'read'; // https://wordpress.org/support/article/roles-and-capabilities/
		$menu_slug  = 'bliskapaczka-imported';
		$function   = [ 'Bliskapaczka_Admin_Component', 'content' ];
        $icon_data = 'data:image/svg+xml;base64, PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE4LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iV2Fyc3R3YV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDE2IDE2IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAxNiAxNjsiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPGc+DQoJPGc+DQoJCTxnPg0KCQkJPHBvbHlnb24gc3R5bGU9ImZpbGw6IzQ0NTM1RDsiIHBvaW50cz0iNi4zNzUsMTEuOTgxIDYuNzkzLDExLjIwMSA2LjM5NCwxMS45OTEgCQkJIi8+DQoJCQk8cGF0aCBzdHlsZT0iZmlsbDojNDQ1MzVEOyIgZD0iTTYuNTg5LDExLjU5NGwwLjAwMiwwLjAwMUM2LjU5MSwxMS41OTQsNi41OSwxMS41OTQsNi41ODksMTEuNTk0eiIvPg0KCQk8L2c+DQoJPC9nPg0KCTxwYXRoIHN0eWxlPSJmaWxsOiM0NDUzNUQ7IiBkPSJNOC4wMDIsMTUuODQ1Yy0wLjQxMywwLTAuODIxLTAuMTA0LTEuMTc5LTAuMzAybC01LjA5NC0yLjU1Yy0wLjc1MS0wLjQyMi0xLjIxOC0xLjIxLTEuMjE4LTIuMDU3DQoJCVY1LjM3OGMwLTAuODQ3LDAuNDY0LTEuNjMzLDEuMjEyLTIuMDU0bDUuMDgxLTIuODU3QzcuMTY3LDAuMjYzLDcuNTgsMC4xNTUsOCwwLjE1NWMwLjQyLDAsMC44MzQsMC4xMDgsMS4xOTYsMC4zMTJsNS4wODEsMi44NTcNCgkJYzAuNzQ4LDAuNDIsMS4yMTIsMS4yMDcsMS4yMTIsMi4wNTR2NS41NThjMCwwLjg0Ny0wLjQ2NCwxLjYzNC0xLjIxMiwyLjA1NGwtMC4wMjQsMC4wMTNMOS4xOCwxNS41NDINCgkJQzguODE3LDE1Ljc0Miw4LjQxMiwxNS44NDUsOC4wMDIsMTUuODQ1eiIvPg0KCTxnPg0KCQk8Zz4NCgkJCTxnPg0KCQkJCTxnPg0KCQkJCQk8cGF0aCBzdHlsZT0iZmlsbDojRjM3MTM1OyIgZD0iTTEwLjUxNSw0LjY2MmMtMC4wNjIsMC0wLjEyNCwwLTAuMTU2LTAuMDMxbC0yLjI3Mi0xLjEyQzguMDI1LDMuNDgsNy45NjMsMy40OCw3LjksMy41MTENCgkJCQkJCWwtMi4xNzksMS4xMkM1LjUzNSw0LjcyNCw1LjMxNyw0LjY2Miw1LjIyNCw0LjQ3NVM1LjE5Myw0LjA3MSw1LjM4LDMuOTc3bDIuMTc5LTEuMTJjMC4yNDktMC4xMjQsMC41OTEtMC4xMjQsMC44NCwwDQoJCQkJCQlsMi4yNzIsMS4xMmMwLjE4NywwLjA5MywwLjI0OSwwLjMxMSwwLjE1NiwwLjQ5OEMxMC43NjQsNC42LDEwLjYzOSw0LjY2MiwxMC41MTUsNC42NjJ6Ii8+DQoJCQkJPC9nPg0KCQkJCTxnPg0KCQkJCQk8cGF0aCBzdHlsZT0iZmlsbDojRkZGRkZGOyIgZD0iTTYuNTYyLDEyLjU5OGMtMC4xNTYsMC0wLjI4LTAuMDMxLTAuNDA1LTAuMTI0bC0zLjE3NC0xLjcxMg0KCQkJCQkJYy0wLjI0OS0wLjE1Ni0wLjQwNS0wLjQwNS0wLjQwNS0wLjcxNlY1LjkwN2MwLTAuMjQ5LDAuMjE4LTAuNDY3LDAuNDY3LTAuNDY3czAuNDY3LDAuMjE4LDAuNDY3LDAuNDY3djQuMTA4bDIuOTI1LDEuNTg3DQoJCQkJCQlWNy45NjFMNC44MTksNy4xNTJDNC42MDIsNy4wMjcsNC41MDgsNi43NDcsNC42MzMsNi41MjlzMC40MDUtMC4zMTEsMC42NTQtMC4xODdsMS42ODEsMC44NA0KCQkJCQkJYzAuMjQ5LDAuMTU2LDAuNDA1LDAuNDA1LDAuNDA1LDAuNzE2djMuODljMCwwLjI4LTAuMTU2LDAuNTYtMC40MDUsMC42ODVDNi44NDIsMTIuNTY3LDYuNjg3LDEyLjU5OCw2LjU2MiwxMi41OTh6DQoJCQkJCQkgTTYuNTkzLDExLjYwMkw2LjU5MywxMS42MDJDNi42MjQsMTEuNjAyLDYuNjI0LDExLjYwMiw2LjU5MywxMS42MDJ6IE02LjUsNy45OTJMNi41LDcuOTkyTDYuNSw3Ljk5MnoiLz4NCgkJCQk8L2c+DQoJCQkJPGc+DQoJCQkJCTxwYXRoIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiBkPSJNOS4xNzYsMTIuNjkyYy0wLjI0OSwwLTAuNDY3LTAuMjE4LTAuNDY3LTAuNDY3VjcuODk5YzAtMC4yOCwwLjE1Ni0wLjUyOSwwLjQwNS0wLjY4NQ0KCQkJCQkJbDMuMjA2LTEuNzEyYzAuMjQ5LTAuMTI0LDAuNTI5LTAuMTI0LDAuNzc4LDAuMDMxYzAuMjQ5LDAuMTI0LDAuMzczLDAuMzczLDAuMzczLDAuNjU0djQuMDc3YzAsMC4yOC0wLjE1NiwwLjUyOS0wLjM3MywwLjY1NA0KCQkJCQkJYzAsMCwwLDAtMC4wMzEsMGwtMS44MzYsMC45MzRjLTAuMjE4LDAuMTI0LTAuNTI5LDAuMDMxLTAuNjIyLTAuMTg3Yy0wLjEyNC0wLjIxOC0wLjAzMS0wLjQ5OCwwLjE4Ny0wLjYyMmwxLjcxMi0wLjg3MVY2LjQ5OA0KCQkJCQkJTDkuNjEyLDguMDIzdjQuMjMzQzkuNjQzLDEyLjUwNSw5LjQyNSwxMi42OTIsOS4xNzYsMTIuNjkyeiBNOS41NSw4LjA1NEw5LjU1LDguMDU0TDkuNTUsOC4wNTR6Ii8+DQoJCQkJPC9nPg0KCQkJPC9nPg0KCQkJPGc+DQoJCQkJPGc+DQoJCQkJCTxwYXRoIHN0eWxlPSJmaWxsOiNGRkZGRkY7IiBkPSJNOC4wMjUsMTUuMTgxYy0wLjMxMSwwLTAuNTkxLTAuMDYyLTAuODcxLTAuMjE4bC01LjA3My0yLjU1Mg0KCQkJCQkJQzEuNTUyLDEyLjEsMS4yMDksMTEuNTQsMS4yMDksMTAuOTQ5di01LjU0YzAtMC42MjIsMC4zNDItMS4xODMsMC44NzEtMS40OTRsNS4wNzMtMi44NjNjMC41MjktMC4zMTEsMS4yMTQtMC4zMTEsMS43NDMsMA0KCQkJCQkJbDUuMDQyLDIuODYzYzAuNTI5LDAuMzExLDAuODcxLDAuODcxLDAuODcxLDEuNDYzdjUuNTRjMCwwLjU5MS0wLjM0MiwxLjE4My0wLjg3MSwxLjQ2M2wtNS4wNzMsMi41NTINCgkJCQkJCUM4LjYxNiwxNS4wODgsOC4zMDUsMTUuMTgxLDguMDI1LDE1LjE4MXogTTguMDI1LDEuMTE0Yy0wLjI0OSwwLTAuNDk4LDAuMDYyLTAuNzQ3LDAuMTg3TDIuMjA1LDQuMTY0DQoJCQkJCQlDMS43MzgsNC40MTMsMS40NTgsNC44OCwxLjQ1OCw1LjQwOXY1LjU0YzAsMC40OTgsMC4yOCwwLjk5NiwwLjc0NywxLjI0NWw1LjA3MywyLjUyMWMwLjQ2NywwLjI0OSwxLjAyNywwLjI0OSwxLjQ5NCwwDQoJCQkJCQlsNS4wNzMtMi41NTJjMC40MzYtMC4yNDksMC43MTYtMC43MTYsMC43MTYtMS4yNDVWNS40MDljMC0wLjQ5OC0wLjI4LTAuOTk2LTAuNzQ3LTEuMjQ1TDguNzQxLDEuMzAxDQoJCQkJCQlDOC41MjMsMS4xNzcsOC4yNzQsMS4xMTQsOC4wMjUsMS4xMTR6Ii8+DQoJCQkJPC9nPg0KCQkJPC9nPg0KCQk8L2c+DQoJPC9nPg0KPC9nPg0KPC9zdmc+DQo=';
		$position   = 27; // https://developer.wordpress.org/reference/functions/add_menu_page/

		add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_data, $position );
	}

		/**
		 * Append scripts required by component
		 */
	public static function registryScripts() {
		$helper = Bliskapaczka_Shipping_Method_Helper::instance();

		$orderListScriptMode = $helper->isSandbox() ? 'stage' : 'prod';

		wp_register_script( 'vue', 'https://unpkg.com/vue', array(), false, false );
		wp_enqueue_script( 'vue' );
		wp_register_script( 'bliskapaczka-admin-component-order-list', 'https://storage.googleapis.com/ecommerce.bliskapaczka.pl/' . $orderListScriptMode . '/order-list.min.js', array(), '', false );
		wp_enqueue_script( 'bliskapaczka-admin-component-order-list' );
		
	}

		/**
		 * Display the component content
		 */
	public static function content() {
		$helper = Bliskapaczka_Shipping_Method_Helper::instance();
		$api_key = null;
		$importer = $helper->shopReportedEngineName();
		
		try {
			$api_key          = $helper->getApiKey();
		} catch (\Exception $e) {
			
		}
		
		if ( null === $api_key || 36 !== mb_strlen( $api_key ) ) {
			$url     = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=bliskapaczka' );
			$content = '<div class="alert alert-danger">' . __( 'Wprowadź klucz dostępu do API Bliskapaczka.pl w <a href="' . esc_html( $url ) . '">ustawieniach pluginu.</a>' ) . '</div>';
		} else {
			$content = '<order-list apikey="' . esc_html( $api_key ) . '" importer="' . esc_html( $importer ) . '"></order-list>';
		}

		echo '<div class="container-fluid bp-component">
	    		<div class="row">
    				<div class="col-12">' . $content . '</div>
    			</div>
    		</div>';
	}
}
