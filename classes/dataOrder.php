<?php
require_once 'businessOrder.php';
require_once 'businessOrderLine.php';
require_once 'businessProduct.php';
require_once 'DBConnection.php';

class DataOrder {
    private $db;

    function __construct()
    {
        $this->db = new DBConnection();
    }

    function createCheckout($businessCart) {

        try {
            $sql = "INSERT INTO shop_order (customer_id, delivery_first_name, delivery_last_name, delivery_address_line1, delivery_address_line2, delivery_postal_code, delivery_city, delivery_country, delivery_email, status_id, order_date)
            values ({$businessCart->user_id}, '{$businessCart->first_name}', '{$businessCart->delivery_last_name}', '{$businessCart->delivery_address_line1}', '{$businessCart->delivery_address_line2}', '{$businessCart->delivery_postal_code}', '{$businessCart->delivery_city}', '{$businessCart->delivery_country}', '{$businessCart->delivery_email}', 1, now())";
            
            $this->db->execute($sql);
            //sending last inserted id back
            return $this->db->connection->insert_id;
        } catch (Exception $e) {
            throw new Exception("Cannot add cart to checkout");
        }
    }

    function createCheckoutLines($businessCart, $orderId) {
        try {
            for ($i=0; $i < count($businessCart->lines); $i++) {
                $sql = "INSERT INTO shop_order_line (order_id, product_id, quantity, unit_price, vat_percentage_id, line_total, status_id)
                values ($orderId, {$businessCart->lines[$i]->product->id}, {$businessCart->lines[$i]->quantity}, {$businessCart->lines[$i]->product->price}, 1, {$businessCart->lines[$i]->linePrice}, 1)";
                $this->db->execute($sql);
            }
            
            return $this->db->connection->insert_id;
        } catch (Exception $e) {
            throw new Exception("Cannot add cart lines to checkout");
        }
    }

    function deleteCart($guid) {
        try {
            $sql = "DELETE FROM shop_cart WHERE guid = '$guid'";
            $this->db->execute($sql);
        } catch (Exception $e) {
            throw new Exception("Cannot delete cart $guid");
        }
    }

    function getorder($orderId) {
        try {
            $sql = "SELECT o.*, ol.id as line_id, ol.order_id, ol.product_id, ol.quantity, ol.unit_price, ol.line_total, p.name, p.price FROM shop_order o INNER JOIN shop_order_line ol ON ol.order_id = o.id INNER JOIN shop_products p ON ol.product_id = p.id WHERE o.id = $orderId";
            $result = $this->db->execute($sql);

            $order = new BusinessOrder();
            //$order = new stdClass();
            while ($row = $result->fetch_assoc()) {
                $order->id = $row['id'];
                $order->order_date = $row['order_date'];
                $order->user_id = $row['customer_id'];
                $order->totalPrice = $row['totalPrice'];
                $order->delivery_first_name = $row['delivery_first_name'];
                $order->delivery_last_name = $row['delivery_last_name'];
                $order->delivery_address_line1 = $row['delivery_address_line1'];
                $order->delivery_address_line2 = $row['delivery_address_line2'];
                $order->delivery_postal_code = $row['delivery_postal_code'];
                $order->delivery_city = $row['delivery_city'];
                $order->delivery_country = $row['delivery_country'];
                $order->delivery_email = $row['delivery_email'];
                $order->delivery_phone = $row['delivery_phone'];
                $order->status_id = $row['status_id'];
                $order_lines = new BusinessOrderLine();
                $order_lines->id = $row['line_id'];
                $order_lines->order_id = $row['order_id'];
                $order_lines->product = new BusinessProduct();
                $order_lines->product->id = $row['product_id'];
                $order_lines->product->name = $row['name'];
                $order_lines->product->price = $row['price'];
                $order_lines->quantity = $row['quantity'];
                $order_lines->linePrice = $row['line_total'];
                
                $totalLines =+ 1;
                $totalPrice += $row['line_total'];

                $order->lines[] = $order_lines;
                
            }
            $order->totalPrice = $totalPrice;
            $order->totalQuantity = $totalLines; 
            return $order;
        } catch (Exception $e) {
            throw new Exception("Cannot get order with orderid: $orderId");
        }
    }
}