<?php

require_once 'config.php';

if (!($conn instanceof PDO)) {
   die('Error: No se pudo conectar con la base de datos.');
}

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
   header('location:login.php');
   exit;
}

if (isset($_POST['add_to_wishlist'])) {
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);

   $check_wishlist = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
   $check_wishlist->execute([$p_name, $user_id]);

   $check_cart = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart->execute([$p_name, $user_id]);

   if ($check_wishlist->rowCount() > 0) {
      $message[] = 'Ya agregado a la lista de deseos!';
   } elseif ($check_cart->rowCount() > 0) {
      $message[] = 'Ya agregado al carrito!';
   } else {
      $conn->prepare("INSERT INTO wishlist(user_id, pid, name, price, image) VALUES(?,?,?,?,?)")
           ->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'Añadido a la lista de deseos!';
   }
}

if (isset($_POST['add_to_cart'])) {
   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);
   $p_qty = filter_var($_POST['p_qty'], FILTER_SANITIZE_STRING);

   $check_cart = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart->execute([$p_name, $user_id]);

   if ($check_cart->rowCount() > 0) {
      $message[] = 'Ya agregado al carrito!';
   } else {
      $check_wishlist = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
      $check_wishlist->execute([$p_name, $user_id]);

      if ($check_wishlist->rowCount() > 0) {
         $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?")->execute([$p_name, $user_id]);
      }

      $conn->prepare("INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)")
           ->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'Añadido al carrito!';
   }
}
?>