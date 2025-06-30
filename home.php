<?php

require_once 'config.php';

/** Validar conexión */
if (!($conn instanceof PDO)) {
   die('Error: No se pudo conectar con la base de datos.');
}

session_start();

/** Validar sesión */
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
   header('location:login.php');
   exit;
}

/** Añadir a la lista de deseos */
if (isset($_POST['add_to_wishlist'])) {
   $pid     = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name  = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);

   try {
      $check_wishlist = $conn->prepare("SELECT 1 FROM wishlist WHERE name = ? AND user_id = ?");
      $check_wishlist->execute([$p_name, $user_id]);

      $check_cart = $conn->prepare("SELECT 1 FROM cart WHERE name = ? AND user_id = ?");
      $check_cart->execute([$p_name, $user_id]);

      if ($check_wishlist->rowCount() > 0) {
         $message[] = '¡Ya agregado a la lista de deseos!';
      } elseif ($check_cart->rowCount() > 0) {
         $message[] = '¡Ya agregado al carrito!';
      } else {
         $insert = $conn->prepare("INSERT INTO wishlist(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
         $insert->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
         $message[] = '¡Añadido a la lista de deseos!';
      }
   } catch (PDOException $e) {
      $message[] = 'Error al añadir a la lista de deseos: ' . htmlspecialchars($e->getMessage());
   }
}

/** Añadir al carrito */
if (isset($_POST['add_to_cart'])) {
   $pid     = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name  = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);
   $p_qty   = filter_var($_POST['p_qty'], FILTER_SANITIZE_STRING);

   try {
      $check_cart = $conn->prepare("SELECT 1 FROM cart WHERE name = ? AND user_id = ?");
      $check_cart->execute([$p_name, $user_id]);

      if ($check_cart->rowCount() > 0) {
         $message[] = '¡Ya agregado al carrito!';
      } else {
         // Eliminar de wishlist si ya estaba ahí
         $check_wishlist = $conn->prepare("SELECT 1 FROM wishlist WHERE name = ? AND user_id = ?");
         $check_wishlist->execute([$p_name, $user_id]);

         if ($check_wishlist->rowCount() > 0) {
            $delete = $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?");
            $delete->execute([$p_name, $user_id]);
         }

         $insert = $conn->prepare("INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         $insert->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
         $message[] = '¡Añadido al carrito!';
      }
   } catch (PDOException $e) {
      $message[] = 'Error al añadir al carrito: ' . htmlspecialchars($e->getMessage());
   }
}
?>