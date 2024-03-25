<?php
/**
 * Callback function Export Orders.
 *
 * @since    1.0.3
 */

function ib_export_orders_callback() {
	$date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
	$date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
	$selected_countries = isset($_POST['selected_countries']) ? json_decode(stripslashes($_POST['selected_countries']), true) : [];
	$status = isset($_POST['selected_statuses']) ? json_decode(stripslashes($_POST['selected_statuses']), true) : [];
	$order_total = isset($_POST['order_total']) ? absint($_POST['order_total']) : '';

	if (empty($date_from) && empty($date_to)) {
		$date_from = '1970-01-01';
		$date_to = date('Y-m-d');
	} else {
		if (empty($date_from)) {
			$date_from = '1970-01-01';
		}
		if (empty($date_to)) {
			$date_to = date('Y-m-d');
		}
	}

	$meta_query = array('relation' => 'AND');

	if (!empty($selected_countries)) {
		$meta_query[] = [
			'key' => '_billing_country',
			'value' => $selected_countries,
			'compare' => 'IN',
		];
	}

	$condition = ['relation' => 'OR'];
	if (!empty($_POST['meta_key'])) {
		$meta_keys = $_POST['meta_key'];
		$meta_values = $_POST['meta_value'] ?? [];

		for ($i = 0; $i < count($meta_keys); $i++) {
			if (!empty($meta_keys[$i])) {
				$key = sanitize_text_field($meta_keys[$i]);
				$value = !empty($meta_values[$i]) ? sanitize_text_field($meta_values[$i]) : '';

				$condition[] = ['key' => $key];
				if ('' !== $value) {
					$condition += ['value' => $value, 'compare' => '='];
				} else {
					$condition += ['compare' => 'EXISTS'];
				}
			}
		}
	}

	if (count($condition) > 1) {
		$meta_query[] = $condition;
	}

	$args = array(
		'post_type' => 'shop_order',
		'post_status' => 'any',
		'date_query' => array(
			array(
				'after'     => $date_from,
				'before'    => $date_to,
				'inclusive' => true,
			),
		),
		'numberposts' => -1,
	);

	if (!empty($status)) {
		$args['post_status'] = $status;
	}

	if (!empty($meta_query)) {
		$args['meta_query'] = $meta_query;
	}

	if (!empty($order_total)) {
		$order_total = floatval($order_total);
	}

	$order_total_operator = '>';

	if (!empty($order_total) && isset($_POST['order_total_operator']) && $_POST['order_total_operator'] === 'less_than') {
		$order_total_operator = '<';
	}

	$posts = get_posts($args);

	$header_args = array( 'Order ID', 'Date', 'Status', 'Items Qty', 'Total Weight', 'Total', 'Country', 'Name', 'Email', 'Phone' );

	$file_pointer = fopen('php://temp', 'w+');

	fputcsv($file_pointer, $header_args);

	foreach ($posts as $post) {
		$order = wc_get_order($post->ID);

		$items_qty = 0;
		$total_weight = 0;
		foreach ($order->get_items() as $item) {
			$product = $item->get_product();
			if ($product) {
				$items_qty += $item->get_quantity();
				$product_weight = $product->get_weight();
				if ($product_weight !== '') {
					$total_weight += $product_weight * $item->get_quantity();
				}
			}
		}

		if ($order_total_operator === '>' && $order->get_total() <= $order_total) {
			continue;
		}
		if ($order_total_operator === '<' && $order->get_total() >= $order_total) {
			continue;
		}

		fputcsv($file_pointer, array(
			esc_html($order->get_id()),
			esc_html($order->get_date_created()->date('Y-m-d')),
			esc_html($order->get_status()),
			esc_html($items_qty),
			esc_html($total_weight),
			esc_html($order->get_total()),
			esc_html($order->get_billing_country()),
			esc_html($order->get_formatted_billing_full_name()),
			esc_html($order->get_billing_email()),
			esc_html($order->get_billing_phone()),
		));
	}

	rewind($file_pointer);

	$csv_data = stream_get_contents($file_pointer);

	fclose($file_pointer);

	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename=exported_orders.csv");
	header("Pragma: no-cache");
	header("Expires: 0");

	echo $csv_data;
	exit;
}
add_action('admin_post_export_orders', 'ib_export_orders_callback');
add_action('admin_post_nopriv_export_orders', 'ib_export_orders_callback');