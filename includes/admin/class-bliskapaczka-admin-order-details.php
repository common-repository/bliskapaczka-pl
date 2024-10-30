<?php
/**
 * Extends WooCommerce Admin Order Detail
 *
 * @author   Bliskapaczka.pl
 * @category Admin
 * @package  Bliskapaczka/Admin/Order
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Operations in WooCommerce admin order details view
 */
class Bliskapaczka_Admin_Order_Details {


	/**
	 * Print information from bliskapaczka.pl shipping
	 *
	 * @param WC_Order $order Woo Commerce order.
	 */
	public function shipping_details( WC_Order $order ) {

		// We take a shiping method, and check for bliskapaczka data.
		$method_id = $this->helper()->getWCShipingMethodId( $order );

		if ( Bliskapaczka_Courier_Shipping_Method::get_identity() !== $method_id
			&& Bliskapaczka_Map_Shipping_Method::get_identity() !== $method_id
			&& false === Bliskapaczka_Flexible_Shipping_Integration::isFlexibleShippingMethod($method_id)
		) {
			return; // Shiping aren't from bliskapaczka, so we do nothing.
		}

		$bliska_order_id = $order->get_meta( '_bliskapaczka_order_id', true, 'view' );

		if ( empty( $bliska_order_id ) ) {
			return; // We didn't have bliskapaczka data.
		}
		
		$operator = $point_code = $point_address = null;
		
		$shipping_items = $order->get_items(['shipping']);
		
		if (is_array($shipping_items) && count($shipping_items) > 0) {
			/**
			 * @var \WC_Order_Item_Shipping $item
			 */
			foreach ( $shipping_items  as $item ) {
				
				if ($item->meta_exists('_bliskapaczka_posOperator')) {
					$operator = $item->get_meta('_bliskapaczka_posOperator', true, 'view');
				}
				
				if ($item->meta_exists('_bliskapaczka_posCode')) {
					$point_code = $item->get_meta('_bliskapaczka_posCode', true, 'view');
					$point_address = $item->get_meta('_bliskapaczka_posInfo', true, 'view');
					break;
				}
			}
		}
		

        try {
            $bliskapaczka_order = $this->helper()->apiOrderGet( $bliska_order_id );
        } catch (Exception $e) {
            echo 'Odpowiedź API: ',  $e->getMessage(), "\n";
        }


		if ( !$bliskapaczka_order ) {
			echo '<div class="alert alert-danger">' . esc_html(sprintf(__('Nie można pobrać szczegółów zamówienia "%s" z API bliskapaczka.pl', 'bliskapaczka_pl'), $bliska_order_id )). '</div>';
			return;
		}
		
		$waybill_urls = $this->helper()->getWaybillUrls( $bliska_order_id );
		
		$download_documents_html = '';

		if ( is_array( $waybill_urls ) && count( $waybill_urls ) > 0 ) {
			foreach ( $waybill_urls as $url ) {
				$download_documents_html .= sprintf( '<div><a href="%s" target="_blank">%s</a></div>', esc_url( $url ), esc_html( __( 'Pobierz list przewozowy', 'bliskapaczka-pl' ) ) );
			}
		}

		if (null !== $bliskapaczka_order && isset($bliskapaczka_order->status) && 'READY_TO_SEND' === $bliskapaczka_order->status) {
			$url = admin_url('admin-ajax.php?action=bliskapaczka_download_pickup&bliska-orders-numbers=' . $bliska_order_id);
			$download_documents_html .= sprintf( '<div><a href="%s" target="_blank">%s</a></div>', esc_url( $url ), esc_html( __( 'Pobierz protokół nadania', 'bliskapaczka-pl' ) ) );
		}
		
		if ( !empty($download_documents_html) ) {
			$download_documents_html = '<tr><td colspan="2">' . $download_documents_html . '</td></tr>';
		}
		$content =
		 '<div class="bliskapaczka-wc-admin-shipping-details">
				<h3>Bliskapaczka.pl - ' . esc_html( $order->get_shipping_method() ) . '</h3>
				<table>
					<tr>
						<td>' . esc_html( __( 'Order number', 'bliskapaczka-pl' ) ) . ':</td>
						<td>' . esc_html( $bliska_order_id ) . '</td>
					</tr>';
		
		if ( null !== $operator ) {
			$content .= '
					<tr>
						<td>' . esc_html( __( 'Operator', 'bliskapaczka-pl' ) ) . ':</td>
						<td>' . esc_html( __($operator, 'bliskapaczka-pl' )) . '</td>
					</tr>';
		}
		
		if ( null !== $point_code ) {
			$content .= '
					<tr>
						<td>' . esc_html( __( 'Point', 'bliskapaczka-pl' ) ) . '</td>
						<td>' . esc_html( __($point_code, 'bliskapaczka-pl' )) . ', ' . esc_html( __($point_address, 'bliskapaczka-pl' ))  . '</td>
					</tr>';
		}
		
		$content .=
					'<tr>
						<td>' . esc_html( __( 'Status', 'bliskapaczka-pl' ) ) . ':</td>
						<td>' . esc_html( __($bliskapaczka_order->status, 'bliskapaczka-pl' )) . '</td>
					</tr>
					' . $download_documents_html . '
				</table>
			</div>';

		echo wp_kses_post( $content );
	}

	/**
	 * Print warning message from bliskapaczka.pl, which was appended to the order meta.
	 *
	 * @param WC_Order $order Woo Commerce order.
	 */
	public function shipping_show_msg_warn( WC_Order $order ) {
		$msg = $order->get_meta( '_bliskapaczka_msg_warn', true, 'view' );

		if ( empty( $msg ) ) {
			return;
		}

		$content = '<div class="bliskapaczka-wc-admin-shipping-details-msg-warn alert alert-warining"><h3>' . __( 'Message from Bliskapaczka.pl', 'bliskapaczka-pl' ) . '</h3>' . $msg . '</div>';

		echo wp_kses_post( $content );
	}

	/**
	 * Returns Bliskapaczaka helper
	 *
	 * @return Bliskapaczka_Shipping_Method_Helper
	 */
	private function helper() {
		return Bliskapaczka_Shipping_Method_Helper::instance();
	}

}

