<?php


class HyperSellOrder
{
    public function insert_order($order_data){
        $order = wc_create_order();

        // add products
        foreach ($order_data['items'] as $product) {
          $order->add_product( wc_get_product(sanitize_mime_type($product['variant_id'])),
           sanitize_mime_type($product['quantity']));
        }

        // add shipping
        $shipping = new WC_Order_Item_Shipping();
        $shipping->set_method_title(sanitize_text_field($order_data['shipping']['methodTitle']));
        $shipping->set_method_id(sanitize_text_field($order_data['shipping']['methodId']));
        $shipping->set_total(sanitize_mime_type($order_data['shipping']['total']));
        $order->add_item($shipping);


        if ($order_data['coupon']) {
            $order->apply_coupon(sanitize_text_field($order_data['coupon']));
        }

        $order->add_order_note(sanitize_textarea_field($order_data['note']));

        if ($order_data['customerIp']) {
            $order->set_customer_ip_address(sanitize_mime_type($order_data['customerIp']));
        }

        // add billing and shipping addresses

        $address = json_decode(json_encode($order_data['address']), true);

        $order->set_address($address, 'billing');
        $order->set_address($address, 'shipping');

        // add payment method
        $order->set_payment_method(sanitize_mime_type($order_data['paymentMethod']['method']));
        $order->set_payment_method_title(sanitize_mime_type($order_data['paymentMethod']['title']));

        // order status
        $order->set_status(sanitize_mime_type($order_data['status']), 'HyperSell');

        // calculate and save
        $order->calculate_totals();
        $result=$order->save();

        return array("orderStatus" => $order->get_checkout_order_received_url(), "id" => $result);
    }

}