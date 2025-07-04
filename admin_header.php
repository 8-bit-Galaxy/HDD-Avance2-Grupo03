<?php

/** @var \PDO $conn */
/** @var string|null $admin_id */

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

      <a href="admin_page.php" class="logo">Admin<span>Totoro</span></a>

      <nav class="navbar">
         <a href="admin_page.php">inicio</a>
         <a href="admin_products.php">productos</a>
         <a href="admin_orders.php">pedidos</a>
         <a href="admin_users.php">usuarios</a>
         <a href="admin_contacts.php">mensajes</a>
         <a href="admin_proveedor.php">proveedor</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $select_profile->execute([$admin_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
         <p><?= $fetch_profile['name']; ?></p>
         <a href="admin_update_profile.php" class="btn">actualizar perfil</a>
         <a href="logout.php" class="delete-btn">cerrar sesion</a>
         <div class="flex-btn">
            <a href="login.php" class="option-btn">iniciar sesion</a>
            <a href="register.php" class="option-btn">registrarse</a>
         </div>
      </div>

   </div>

</header>