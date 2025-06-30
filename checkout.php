<?php

require_once 'config.php';

/** Validar conexión */
if (!($conn instanceof PDO)) {
   die('Error: No se pudo establecer la conexión con la base de datos.');
}

session_start();

/** Validar sesión */
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
   header('location:login.php');
   exit;
}

/** Procesar pedido */
if (isset($_POST['order'])) {
   $name    = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $number  = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $email   = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   $method  = filter_var($_POST['method'], FILTER_SANITIZE_STRING);

   // Construir dirección completa
   $address = 'Flat No. ' . filter_var($_POST['flat'], FILTER_SANITIZE_STRING) . ' '
            . filter_var($_POST['street'], FILTER_SANITIZE_STRING) . ' '
            . filter_var($_POST['city'], FILTER_SANITIZE_STRING) . ' '
            . filter_var($_POST['state'], FILTER_SANITIZE_STRING) . ' '
            . filter_var($_POST['country'], FILTER_SANITIZE_STRING) . ' - '
            . filter_var($_POST['pin_code'], FILTER_SANITIZE_STRING);

   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products = [];

   try {
      // Obtener carrito
      $cart_query = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
      $cart_query->execute([$user_id]);

      if ($cart_query->rowCount() > 0) {
         while ($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)) {
            $cart_products[] = $cart_item['name'] . ' (' . $cart_item['quantity'] . ')';
            $cart_total += $cart_item['price'] * $cart_item['quantity'];
         }

         $total_products = implode(', ', $cart_products);

         // Verificar duplicado
         $order_check = $conn->prepare("SELECT 1 FROM orders WHERE name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_price = ?");
         $order_check->execute([$name, $number, $email, $method, $address, $total_products, $cart_total]);

         if ($order_check->rowCount() > 0) {
            $message[] = '¡Pedido ya realizado!';
         } else {
            // Insertar pedido
            $insert = $conn->prepare("INSERT INTO orders(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES(?,?,?,?,?,?,?,?,?)");
            $insert->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on]);

            // Vaciar carrito
            $conn->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

            $message[] = '¡Pedido realizado con éxito!';
         }

      } else {
         $message[] = 'Tu carrito está vacío.';
      }

   } catch (PDOException $e) {
      $message[] = 'Error al procesar el pedido: ' . htmlspecialchars($e->getMessage());
   }
}
?>