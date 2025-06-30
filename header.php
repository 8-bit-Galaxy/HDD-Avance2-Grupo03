<?php
/** @var \PDO $conn */
/** @var int|string|null $user_id */

if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<header class="header">

   <div class="flex">

      <a href="home.php" class="logo">
         <span class="bit">8-Bit</span> <span class="galaxy">Galaxy</span>
      </a>

      <nav class="navbar">
         <a href="home.php">Inicio</a>
         <a href="shop.php">Tienda</a>
         <a href="orders.php">Pedidos</a>
         <a href="about.php">Acerca</a>
         <a href="contact.php">Contacto</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <a href="search_page.php" class="fas fa-search"></a>
         <?php
            $count_cart_items = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);

            $count_wishlist_items = $conn->prepare("SELECT * FROM wishlist WHERE user_id = ?");
            $count_wishlist_items->execute([$user_id]);
         ?>
         <a href="wishlist.php"><i class="fas fa-heart"></i><span>(<?= $count_wishlist_items->rowCount(); ?>)</span></a>
         <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?= $count_cart_items->rowCount(); ?>)</span></a>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $select_profile->execute([$user_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
         <p><?= $fetch_profile['name']; ?></p>
         <a href="user_profile_update.php" class="btn">Actualizar perfil</a>
         <a href="logout.php" class="delete-btn">cerrar sesión</a>
         <div class="flex-btn">
            <a href="login.php" class="option-btn">iniciar sesión</a>
            <a href="register.php" class="option-btn">regístrate</a>
         </div>
      </div>

   </div>

</header>