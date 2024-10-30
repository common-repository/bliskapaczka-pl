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
 * BliskaPaczka shipping documents downloads.
 * 
 * @author marko
 *
 */
class Bliskapaczka_Admin_Shipping_Documents
{
	/**
	 * Documents download column identity 
	 * 
	 * @var string
	 */
	const COL_DOCS_IDENTITY = 'bliskapaczka_docs';
	
	
	/**
	 * Registry WordPress hooks, filters action
	 */
	public static function registry() {
		// manage_[post_type]_posts_columns
		add_filter( 'manage_edit-shop_order_columns', [ 'Bliskapaczka_Admin_Shipping_Documents' , 'columnsHeader' ]);
		// manage_[post_type]_posts_custom_column
		add_action( 'manage_shop_order_posts_custom_column', [ 'Bliskapaczka_Admin_Shipping_Documents', 'columnsContent' ]);
		
		add_action ( 'wp_ajax_bliskapaczka_download_docs', ['Bliskapaczka_Admin_Shipping_Documents', 'content'] );
		add_action ( 'wp_ajax_bliskapaczka_download_pickup', ['Bliskapaczka_Admin_Shipping_Documents', 'downloadPickup'] );
		
		add_action ( 'wp_ajax_bliskapaczka_pickup_multi', ['Bliskapaczka_Admin_Shipping_Documents', 'pickupMulti'] );
		
		add_action ( 'wp_ajax_bliskapaczka_waybill_multi', ['Bliskapaczka_Admin_Shipping_Documents', 'waybillMulti'] );
		
		add_action( 'admin_head-edit.php', [ 'Bliskapaczka_Admin_Shipping_Documents', 'downloadButtonsOnList' ] );
		
	}

	/**
	 * Hook display columsn
	 * 
	 * @param array $columns
	 * @return string[]
	 */
	public static function columnsHeader( $columns ) 
	{
		$new_columns = array();
		
		// The identity of column after which we add a new. 
		$col_identity_after = 'order_total';
		
		// The new column definition. 
		$col_identity = self::COL_DOCS_IDENTITY;
		$col_label = "BliskaPaczka";
		
		foreach ( $columns as $column_name => $column_info ) {
			
			$new_columns[ $column_name ] = $column_info;
			
			if ( $col_identity_after === $column_name ) {
				$new_columns[$col_identity] = $col_label;
			}
		}
		
		return $new_columns;
		
		$columns['bliskapaczka_docs'] = 'BliskaPaczka';
		return $columns;
	}
	
	/**
	 * Column content
	 * 
	 * @param string $column
	 */
	public static function columnsContent( $column ) 
	{
		
		global $post;
		
		if ( self::COL_DOCS_IDENTITY === $column ) {
			$order    = wc_get_order( $post->ID );
			
			$bliska_order_id = $order->get_meta( '_bliskapaczka_order_id', true, 'view' );
			
			if ( empty( $bliska_order_id ) ) {
				_e( 'n/a' ); // We didn't have bliska paczka data.
				return;
			}
			
			$url = admin_url('admin-ajax.php?action=bliskapaczka_download_docs&shop-order-id='.$post->ID.'&bliska-order=' . $bliska_order_id);
			
			$content =  '<a href="'. $url . '#TB_iframe=true&width=700&height=200" class="thickbox" title="' . esc_html(__( 'Dokumenty przewozowe', 'bliskapaczka-pl' )) . '">' . esc_html(__( 'Pobierz dokumenty przewozowe', 'bliskapaczka-pl' )) . '</a>';
			
			echo wp_kses_post( $content );
		}
			
	}
	
	/**
	 * Download document popup content
	 */
	public static function content() {
		
		$helper = Bliskapaczka_Shipping_Method_Helper::instance();
		
		$shop_order_id = intval($_GET['shop-order-id']);
		$bliska_order_id = mb_strtoupper(sanitize_title_for_query($_GET['bliska-order']));

		if ($shop_order_id <= 0 || mb_strlen($bliska_order_id) < 6) {
			echo wp_kses_post('<div class="alert alert-warning">' . __('Błędne wywołanie.', 'bliskapaczka_pl') .'</div>');
			exit();
		}
		
		$item = [
			'shop_order_id' => $shop_order_id,
			'order_number' => $bliska_order_id,
			'waybills_urls' => [],
			'pickup_report_url' => null,
			'errors' => []
		];
		
		try {
			$item['waybills_urls'] = $helper->getWaybillUrls( $bliska_order_id );
		
			$bliskapaczkaOrder = $helper->apiOrderGet( $bliska_order_id );
		
			if (null !== $bliskapaczkaOrder && isset($bliskapaczkaOrder->status) && 'READY_TO_SEND' === $bliskapaczkaOrder->status)
			{
				$item['pickup_report_url'] = 
					admin_url('admin-ajax.php?action=bliskapaczka_download_pickup&bliska-orders-numbers=' . $bliska_order_id);
			}
		} catch (\Exception $e) {
			$item['errors'][] = $e->getMessage();
		}
		
		echo self::renderWaybillViewDocument([$item]);
		exit();
	}
	
	/**
	 * Append buttons for delivery on shoper orders list. 
	 */
	public static function downloadButtonsOnList() 
	{
		global $current_screen;

		// Not our post type, exit earlier
		if( 'shop_order' !== $current_screen->post_type ) {
			return;
		}

		$bliskapaczka_msg_choose_orders = __('Wybierz zamówienia', 'bliskapaczka_pl');
		$pickup_multi_label = esc_html(__('Pobierz protokoły nadania', 'bliskapaczka_pl'));
		$pickup_multi_url = admin_url('admin-ajax.php?action=bliskapaczka_pickup_multi');
		
		$waybill_multi_label = esc_html(__('Pobierz listy przewozowe', 'bliskapaczka_pl'));
		$waybill_multi_url = admin_url('admin-ajax.php?action=bliskapaczka_waybill_multi');
		
		$script = <<<SCRIPT
		<script type="text/javascript">
			const bliskapaczka_report_pickup_multi_label = '$pickup_multi_label';
			const bliskapaczka_report_pickup_multi_url = '$pickup_multi_url';
			
			const bliskapaczka_waybill_multi_label = '$waybill_multi_label';
			const bliskapaczka_waybill_multi_url = '$waybill_multi_url';
			
			const bliskapaczka_msg_choose_orders_bulk = '$bliskapaczka_msg_choose_orders';

			jQuery(document).ready( function() {
				bliskapaczkaInitDownloadButtonsOnList();
			
			});
		</script>';
SCRIPT;
		echo $script;
	}
	
	/**
	 * Render a view with full html content of waybill download content
	 * @param array $items
	 * @return string
	 */
	public static function renderWaybillViewDocument($items)
	{
		$content = self::renderWaybillViewContent($items);
		
		$document = <<<DOCUMENT
			<html>
				<head>
					<meta charset="UTF-8">
				</head>
				<body>
					$content
				</body>
			</html>
DOCUMENT;
							
		return $document;
	}
	
	/**
	 * Render waybill content view
	 * 
	 * @param array $items
	 */
	private static function renderWaybillViewContent($items)
	{
		$label_shop_id = __('Nr. zamówienia', 'bliskapaczka_pl');
		$label_bliska_id = __('Nr. BliskaPaczka', 'bliskapaczka_pl');
		
		$rows = '';
		
		foreach ($items as $item) {
			$rows .= '<tr class="bp-download-tr">';
			$rows .= '<td class="bp-download-td">' . esc_html($item['shop_order_id']) . '</td>';
			$rows .= '<td class="bp-download-td">' . esc_html($item['order_number']) . '</td>';
			$rows .= '<td class="bp-download-buttons">';
			
			if (count($item['errors']) > 0) {
				foreach ($item['errors'] as $e) {
					$rows .= '<div class="text-danger">' . esc_html($e) .'</div>'; 
				}
			} else {
				if (count($item['waybills_urls']) > 0 ) {
					foreach ($item['waybills_urls'] as $u) {
						$rows .= '<p><a class="bp-download-link" href="'. esc_url($u).'" target="_blank">' . esc_html(__('Pobierz list przewozowy', 'bliskapaczka_pl')) . '</a></p>';
					}
				} else {
					$rows .= '<p>'. esc_html(__('Aktualnie brak dokumentów przewozowych.', 'bliskapaczka_pl')) .'<p>';
				}
				
				if ( null !== $item['pickup_report_url'] ) {
					$rows .= '<p><a class="bp-download-link" href="'. esc_url($item['pickup_report_url']).'" target="_blank">' . esc_html(__('Pobierz dokument nadania', 'bliskapaczka_pl')) . '</a></p>';
				}
			}
			$rows .= '</td>';
			$rows .= '</tr>';
		}
		$content = <<<CONTENT
			<html>
				<head>
					<meta charset="UTF-8">
					<style>
					table.bp-download-table{
					font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
					font-size:14px;
					width:100%;
					}
					th.bp-download-th {
					text-align: left;
					padding-top:10px;
					padding-bottom:10px;
					}
					th.bp-download-th, td.bp-download-td {
                    padding-left: 10px;
                    }
                    td.bp-download-tr{
                    padding:10px 0 10px 0;
                    }
                    a.bp-download-link {
                    font-size:14px;
                    text-decoration: none; 
                    background: #E55F29; 
                    color: white; 
                    border: solid 1px #E55F29; 
                    border-radius: 5px; 
                    white-space: nowrap; 
                    padding:5px; 
                    margin-left:5px;
                    }
                    .bp-download-buttons{
                    display: inline-flex;
                    flex-direction: row;  
                    justify-content: center; 
                    align-items: center;
                    }
                    .bp-download-buttons p{
                    margin:0 0 0 5px;
                    }
                    </style>
				</head>
				<body>
					<table class="bp-download-table">
						<thead>
							<tr>
								<th class="bp-download-th">$label_shop_id</th>
								<th class="bp-download-th">$label_bliska_id</th>
								<th class="bp-download-th"></th>
							</tr>
						</thead>
						<tbody>
							$rows
						</tbody>
					</table>
				</body>
			</html>
CONTENT;
		
		return $content;
	}
	
	/**
	 * Download pickup document.
	 */
	public static function downloadPickup() 
	{
		$helper = Bliskapaczka_Shipping_Method_Helper::instance();
		$numbers = mb_strtoupper(sanitize_title_for_query($_GET['bliska-orders-numbers']));
		$content = null;
		
		try {
			$content = $helper->apiPickupReportGet($numbers);
		} catch (\Exception $e) {
			echo $e->getMessage();
			exit();
		}
		
		if (null === $content || empty($content)) {
			printf(__('Operator nie zwrócił zawartości protokołu nadania dla zamówień "%s"', 'bliskapaczka_pl'), str_replace(',', ', ', $numbers));
			exit();
		}
		
		header('Content-Type: application/pdf', true);
		header('Content-Disposition: attachment; filename="pickup_confirmation_' . date("Y-m-d_his") . '.pdf"', true);
		header('Cache-Control: no-cache, must-revalidate', true);
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT', true);
		
		echo $content;
		exit();
	}
	
	/**
	 *  Generates view with links to pickups confirmations for given post data.
	 */
	public static function pickupMulti() 
	{
		$helper = Bliskapaczka_Shipping_Method_Helper::instance();
		
		if (!isset($_POST) || !isset($_POST['orders']) || !is_array($_POST['orders']) || 0 === count($_POST['orders']) ) {
			echo json_encode(['content' => '<div class="alert alert-danger">' . esc_html(__('Błędne wywołanie. Nie przekazano identyfikatorów zamówień', 'bliskapaczka_pl')) . '</div>']);
			exit();
		}
		
		$problems = $operators = $links = [];
		
		$reportProblem  = function($shop_order_id, $message) use(&$problems) {
			if (!isset($problems[$shop_order_id])) {
				$problems[$shop_order_id] = [];
			}
			$problems[$shop_order_id][] = $message;
		};
		
		foreach ($_POST['orders'] as $shop_order_id) {
			$shop_order_id = intval($shop_order_id);
			
			if (0 > $shop_order_id) {
				continue;
			}
			
			$shop_order = wc_get_order($shop_order_id);
			
			if ( ! ( $shop_order instanceof  WC_Order ) ) {
				$reportProblem($shop_order_id, __('Brak zamówienia o zadanym identyfikatorze.', 'bliskapaczka_pl') );
				continue;
			}
			
			$bliska_order_id = $shop_order->get_meta( '_bliskapaczka_order_id', true, 'view' );
			$bliska_order = $helper->apiOrderGet($bliska_order_id);
			
			if (! $bliska_order ) {
				$reportProblem($shop_order_id, __('Zamówienie nie zostało przesłane do bliskapaczka.pl.', 'bliskapaczka_pl') );
				continue;
			}
			
			if ('READY_TO_SEND' !== $bliska_order->status) {
				$reportProblem($shop_order_id, __('Zamówienie nie zostało przekazane do nadania', 'bliskapaczka_pl') );
				continue;
			}
			
			if (!isset($operators[ $bliska_order->operatorName])) {
				$operators[$bliska_order->operatorName] = [];
			}
			
			$operators[$bliska_order->operatorName][] = $bliska_order->number;
		}
		
		if (count($operators) > 0) {
			foreach ($operators  as $operator => $numbers) {
				$links[$operator] = admin_url('admin-ajax.php?action=bliskapaczka_download_pickup&bliska-orders-numbers=' . implode(',', $numbers));
			}
		}
		
		echo json_encode(['content' => self::renderPickupMultiLinksView($links, $problems)]);
		exit();
	}
	
	/**
	 * Process multi waybill action
	 */
	public static function waybillMulti() 
	{
		$helper = Bliskapaczka_Shipping_Method_Helper::instance();
		
		if (!isset($_POST) || !isset($_POST['orders']) || !is_array($_POST['orders']) || 0 === count($_POST['orders']) ) {
			echo json_encode(['content' => '<div class="alert alert-danger">' . esc_html(__('Błędne wywołanie. Nie przekazano identyfikatorów zamówień', 'bliskapaczka_pl')) . '</div>']);
			exit();
		}
		
		$items = [];
		
		foreach ($_POST['orders'] as $shop_order_id) {
			$shop_order_id = intval($shop_order_id);
			
			if (0 > $shop_order_id) {
				continue;
			}
			
			$shop_order = wc_get_order($shop_order_id);
			
			if ( ! ( $shop_order instanceof  WC_Order ) ) {
				continue;
			}
			
			$bliska_order_id = $shop_order->get_meta( '_bliskapaczka_order_id', true, 'view' );
			
			$item = [
				'shop_order_id' => $shop_order_id,
				'order_number' => $bliska_order_id,
				'waybills_urls' => [],
				'pickup_report_url' => null,
				'errors' => []
			];
			
			if (null !== $bliska_order_id) {
				
				try {
					$item['waybills_urls'] = $helper->getWaybillUrls( $bliska_order_id );
					
				} catch (\Exception $e) {
					$item['errors'][] = $e->getMessage();
				}
			} else {
				$item['errors'][] = __('Zamówienie nie zostało wysłane do bliskapaczka.pl');
			}
			
			$items[] = $item;
		}
		
		echo json_encode(['content' => self::renderWaybillViewContent($items)]);
		exit();
	}
	/**
	 * Render view for pickup confirmation documents.
	 * 
	 * @param array $links Array where key is operator identity and value is a link to documents
	 * 		$links = [
	 * 			'DPD' => 'https://example.com/doc_1'
	 * 			'UPS' => 'https://example.com/doc_2
	 * 		];
	 * @param array $problems Multi array. Key must be a shop order id, value a array of problems
	 *  $problmes = [ 
	 *  	'ID_1' => [ 'problem first', 'problem second'],
	 *  	'ID_2' => [ 'other problem']
	 *   ]
	 * @return string
	 */
	private static function renderPickupMultiLinksView($links, $problems)
	{
		$label_operator = __('Operator', 'bliskapaczka_pl');
		
		$rows = '';
		
		if ( is_array($links) && count($links) > 0) {
			foreach ($links as $operator => $link) {
				$rows .= '<tr class="bp-download-tr">';
				$rows .= '<td class="bp-download-td">' . esc_html($operator) . '</td>';
				$rows .= sprintf('<td><p><a class="bp-download-link" href="%s" target="_blank">%s</a></p></td>', esc_url($link), __('Pobierz protokół nadania', 'bliskapaczka_pl'));
				$rows .= '</tr>';
			}
		} else {
			$rows .= sprintf('<tr><td colspan="2"><p>%s</p></td></td>', __('Brak protokołów nadania dla wybranych zamówień', 'bliskapaczka_pl'));
		}
		
		$content = <<<CONTENT
				<style>
					table.bp-download-table{
					font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
					font-size:14px;
					width:100%;
					}
					th.bp-download-th {
					text-align: left;
					padding-top:10px;
					padding-bottom:10px;
					}
					th.bp-download-th, td.bp-download-td {
                    padding-left: 10px;
                    }
                    td.bp-download-tr{
                    padding:10px 0 10px 0;
                    }
                    a.bp-download-link {
                    font-size:14px;
                    text-decoration: none; 
                    background: #E55F29; 
                    color: white; 
                    border: solid 1px #E55F29; 
                    border-radius: 5px; 
                    white-space: nowrap; 
                    padding:5px; 
                    margin-left:5px;
                    }
                    </style>
			<table class="bp-download-table">
				<thead>
					<tr class="bp-download-tr">
						<th class="bp-download-th">$label_operator</th>
						<th class="bp-download-th"></th>
					</tr>
				</thead>
				<tbody>
					$rows
				</tbody>
			</table>
CONTENT;

		$problems = '';
		
		if (is_array($problems) && count($problems) > 0) {
			foreach ($problems as $oid => $messages) {
				$problems .= '<div class="alert alert-danger">';
				$problems .= '<strong>' . esc_html(sprintf(__('Wystąpił problem podczas pobierania protokołu nadania dla zamówienia "%s"', 'bliskapaczka_pl'), $oid)) .'</strong>';
				$problems .= '<ul>';
				foreach ($messages as $msg) {
					$problems .= '<li>' . esc_html($msg) . '</li>';
				}
				$problems .= '<ul>';
				$problems .= '</div>';
			}
		}
		
		return $content . $problems;
	}
}