<?php

@include 'config.php';

/** @var \PDO $conn */
/** @var string|null $admin_id */
/** @var string|null $user_id */

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){
   header('location:login.php');
   exit;
}

if(isset($_POST['add_to_wishlist'])){

   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);

   $check_wishlist_numbers = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
   $check_wishlist_numbers->execute([$p_name, $user_id]);

   $check_cart_numbers = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_wishlist_numbers->rowCount() > 0){
      $message[] = 'Ya agregado a la lista de deseos!';
   } elseif($check_cart_numbers->rowCount() > 0){
      $message[] = 'Ya agregado al carrito!';
   } else {
      $insert_wishlist = $conn->prepare("INSERT INTO wishlist(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
      $insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'Añadido a la lista de deseos!';
   }
}

if(isset($_POST['add_to_cart'])){

   $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
   $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
   $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
   $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);

   $check_cart_numbers = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_cart_numbers->rowCount() > 0){
      $message[] = 'Ya agregado al carrito!';
   } else {
      $check_wishlist_numbers = $conn->prepare("SELECT * FROM wishlist WHERE name = ? AND user_id = ?");
      $check_wishlist_numbers->execute([$p_name, $user_id]);

      if($check_wishlist_numbers->rowCount() > 0){
         $delete_wishlist = $conn->prepare("DELETE FROM wishlist WHERE name = ? AND user_id = ?");
         $delete_wishlist->execute([$p_name, $user_id]);
      }

      $insert_cart = $conn->prepare("INSERT INTO cart(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, 1, $p_image]);
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
   <title>Tienda</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="p-category">
   <a href="category.php?category=Consolas">Consolas</a>
   <a href="category.php?category=Videojuegos">Videojuegos</a>
   <a href="category.php?category=Perifericos">Periféricos</a>
</section>

<section class="products">
   <h1 class="title">
      <span class="word-red">Pro</span><span class="word-green">duc</span><span class="word-blue">tos</span>
   </h1>

   <div class="box-container">
   <?php
      $select_products = $conn->prepare("SELECT * FROM products");
      $select_products->execute();
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" class="box" method="POST">
      <div class="price">S/.<span><?= htmlspecialchars($fetch_products['price']) ?></span></div>
      <a href="view_page.php?pid=<?= htmlspecialchars($fetch_products['id']) ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']) ?>" alt="">
      <div class="name"><?= htmlspecialchars($fetch_products['name']) ?></div>
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_products['id']) ?>">
      <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']) ?>">
      <input type="hidden" name="p_price" value="<?= htmlspecialchars($fetch_products['price']) ?>">
      <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['image']) ?>">
      <input type="submit" value="Añadir a la lista de deseos" class="option-btn" name="add_to_wishlist">
      <input type="submit" value="Añadir al carrito" class="btn" name="add_to_cart">
   </form>
   <?php
         }
      } else {
         echo '<p class="empty">¡Aún no se han añadido productos!</p>';
      }
   ?>
   </div>
</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>