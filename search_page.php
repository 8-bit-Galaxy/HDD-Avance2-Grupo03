<?php

require_once 'config.php';

if (!($conn instanceof PDO)) {
   die('Error: No se pudo establecer conexión con la base de datos.');
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
      $insert = $conn->prepare("INSERT INTO wishlist(user_id, pid, name, price, image) VALUES (?, ?, ?, ?, ?)");
      $insert->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
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
         $delete = $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?");
         $delete->execute([$p_name, $user_id]);
      }

      $insert = $conn->prepare("INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
      $insert->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'Añadido al carrito!';
   }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Página de búsqueda</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="search-form">
   <form action="" method="POST">
      <input type="text" class="box" name="search_box" placeholder="Buscar producto..." required>
      <input type="submit" name="search_btn" value="Buscar" class="btn">
   </form>
</section>

<section class="products" style="padding-top: 0; min-height:100vh;">
   <div class="box-container">

   <?php
   if (isset($_POST['search_btn'])) {
      $search_box = filter_var($_POST['search_box'], FILTER_SANITIZE_STRING);
      $query = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR category LIKE ?");
      $like = "%$search_box%";
      $query->execute([$like, $like]);

      if ($query->rowCount() > 0) {
         while ($product = $query->fetch(PDO::FETCH_ASSOC)) {
   ?>
   <form action="" class="box" method="POST">
      <div class="price">S/.<span><?= htmlspecialchars($product['price']) ?></span></div>
      <a href="view_page.php?pid=<?= $product['id'] ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= htmlspecialchars($product['image']) ?>" alt="">
      <div class="name"><?= htmlspecialchars($product['name']) ?></div>
      <input type="hidden" name="pid" value="<?= $product['id'] ?>">
      <input type="hidden" name="p_name" value="<?= htmlspecialchars($product['name']) ?>">
      <input type="hidden" name="p_price" value="<?= $product['price'] ?>">
      <input type="hidden" name="p_image" value="<?= $product['image'] ?>">
      <input type="number" min="1" value="1" name="p_qty" class="qty">
      <input type="submit" value="Agregar a la lista de deseos" class="option-btn" name="add_to_wishlist">
      <input type="submit" value="Agregar al carrito" class="btn" name="add_to_cart">
   </form>
   <?php
         }
      } else {
         echo '<p class="empty">¡No se encontraron resultados!</p>';
      }
   }
   ?>

   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>