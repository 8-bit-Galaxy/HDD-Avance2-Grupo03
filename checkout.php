<?php

require_once 'config.php';

/** @var PDO $conn */
if (!isset($conn) || !$conn instanceof PDO) {
   die('Error: No se pudo establecer la conexión a la base de datos.');
}

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
   header('location:login.php');
   exit;
}

if (isset($_POST['order'])) {

   // Sanitizar entradas
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
         $sub_total = $cart_item['price'] * $cart_item['quantity'];
         $cart_total += $sub_total;
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

         $delete_cart = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
         $delete_cart->execute([$user_id]);

         $message[] = '¡Pedido realizado con éxito!';
      }
   }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Finalizar pedido</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- CSS personalizado -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="display-orders">
   <h2 class="title">Resumen del pedido</h2>
   <?php
      $cart_grand_total = 0;
      $select_cart_items = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
      $select_cart_items->execute([$user_id]);

      if ($select_cart_items->rowCount() > 0) {
         while ($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)) {
            $total_price = $fetch_cart_items['price'] * $fetch_cart_items['quantity'];
            $cart_grand_total += $total_price;
   ?>
   <p><?= htmlspecialchars($fetch_cart_items['name']) ?> <span>(S/.<?= $fetch_cart_items['price'] ?> x <?= $fetch_cart_items['quantity'] ?>)</span></p>
   <?php
         }
         echo '<div class="grand-total">Gran total : <span>S/.' . $cart_grand_total . '</span></div>';
      } else {
         echo '<p class="empty">Tu carrito está vacío!</p>';
      }
   ?>
</section>

<section class="checkout-orders">

   <form action="" method="POST">
      <h3>Hacer pedido</h3>

      <div class="flex">
         <div class="inputBox">
            <span>Tu nombre:</span>
            <input type="text" name="name" placeholder="Ingrese su nombre" class="box" required>
         </div>
         <div class="inputBox">
            <span>Tu número:</span>
            <input type="number" name="number" placeholder="Ingrese su número" class="box" required>
         </div>
         <div class="inputBox">
            <span>Tu correo:</span>
            <input type="email" name="email" placeholder="Ingrese su email" class="box" required>
         </div>
         <div class="inputBox">
            <span>Método de pago:</span>
            <select name="method" class="box" required>
               <option value="Envío contra reembolso">Envío contra reembolso</option>
               <option value="Tarjeta de crédito">Tarjeta de crédito</option>
               <option value="Yape">Yape</option>
               <option value="Efectivo">Efectivo</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Línea 1 (número):</span>
            <input type="text" name="flat" placeholder="Número de dirección" class="box" required>
         </div>
         <div class="inputBox">
            <span>Línea 2 (calle):</span>
            <input type="text" name="street" placeholder="Nombre de la calle" class="box" required>
         </div>
         <div class="inputBox">
            <span>Ciudad:</span>
            <input type="text" name="city" placeholder="Lima" class="box" required>
         </div>
         <div class="inputBox">
            <span>Provincia:</span>
            <input type="text" name="state" placeholder="Lima" class="box" required>
         </div>
         <div class="inputBox">
            <span>País:</span>
            <input type="text" name="country" placeholder="Perú" class="box" required>
         </div>
         <div class="inputBox">
            <span>Código postal:</span>
            <input type="number" min="0" name="pin_code" placeholder="123456" class="box" required>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($cart_grand_total > 0) ? '' : 'disabled'; ?>" value="Realizar pedido">
   </form>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>