<?php

require_once 'config.php';

if (!($conn instanceof PDO)) {
   die('Error: No se pudo establecer la conexión con la base de datos.');
}

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
   header('location:login.php');
   exit;
}

if (isset($_POST['order'])) {
   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
   $address = 'Flat No. ' . filter_var($_POST['flat'], FILTER_SANITIZE_STRING) . ' '
            . filter_var($_POST['street'], FILTER_SANITIZE_STRING) . ' '
            . filter_var($_POST['city'], FILTER_SANITIZE_STRING) . ' '
            . filter_var($_POST['state'], FILTER_SANITIZE_STRING) . ' '
            . filter_var($_POST['country'], FILTER_SANITIZE_STRING) . ' - '
            . filter_var($_POST['pin_code'], FILTER_SANITIZE_STRING);
   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products = [];

   $cart_query = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
   $cart_query->execute([$user_id]);

   if ($cart_query->rowCount() > 0) {
      while ($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)) {
         $cart_products[] = $cart_item['name'] . ' (' . $cart_item['quantity'] . ')';
         $cart_total += $cart_item['price'] * $cart_item['quantity'];
      }
   }

   $total_products = implode(', ', $cart_products);

   if ($cart_total == 0) {
      $message[] = 'Tu carrito está vacío';
   } else {
      $order_query = $conn->prepare("SELECT * FROM orders WHERE name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_price = ?");
      $order_query->execute([$name, $number, $email, $method, $address, $total_products, $cart_total]);

      if ($order_query->rowCount() > 0) {
         $message[] = '¡Pedido ya realizado!';
      } else {
         $insert_order = $conn->prepare("INSERT INTO orders(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES(?,?,?,?,?,?,?,?,?)");
         $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on]);

         $conn->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

         $message[] = '¡Pedido realizado con éxito!';
      }
   }
}
?>