<?php

require_once 'config.php';

if (!($conn instanceof PDO)) {
   die('Error: No se pudo conectar a la base de datos.');
}

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
   header('location:login.php');
   exit;
}

// Obtener información del perfil del usuario
$profile_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$profile_query->execute([$user_id]);

if ($profile_query->rowCount() > 0) {
   $fetch_profile = $profile_query->fetch(PDO::FETCH_ASSOC);
} else {
   die('Usuario no encontrado.');
}

if (isset($_POST['update_profile'])) {

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);

   $update_profile = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
   $update_profile->execute([$name, $email, $user_id]);

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/' . $image;
   $old_image = $_POST['old_image'];

   if (!empty($image)) {
      if ($image_size > 2000000) {
         $message[] = '¡El tamaño de la imagen es demasiado grande!';
      } else {
         $update_image = $conn->prepare("UPDATE users SET image = ? WHERE id = ?");
         $update_image->execute([$image, $user_id]);
         move_uploaded_file($image_tmp_name, $image_folder);
         if ($old_image && file_exists('uploaded_img/' . $old_image)) {
            unlink('uploaded_img/' . $old_image);
         }
         $message[] = '¡Imagen actualizada exitosamente!';
      }
   }

   $old_pass = $_POST['old_pass'];
   $update_pass = md5($_POST['update_pass']);
   $new_pass = md5($_POST['new_pass']);
   $confirm_pass = md5($_POST['confirm_pass']);

   if (!empty($_POST['update_pass']) && !empty($_POST['new_pass']) && !empty($_POST['confirm_pass'])) {
      if ($update_pass != $old_pass) {
         $message[] = '¡La contraseña anterior no coincide!';
      } elseif ($new_pass != $confirm_pass) {
         $message[] = '¡Confirmación de contraseña no coincide!';
      } else {
         $update_pass_query = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
         $update_pass_query->execute([$confirm_pass, $user_id]);
         $message[] = '¡Contraseña actualizada exitosamente!';
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
   <title>Actualizar perfil</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/components.css">
</head>
<body>

<?php include 'header.php'; ?>

<?php
if (isset($message)) {
   foreach ($message as $msg) {
      echo '
      <div class="message">
         <span>' . htmlspecialchars($msg) . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>';
   }
}
?>

<section class="update-profile">
   <h1 class="title">Actualizar perfil</h1>

   <form action="" method="POST" enctype="multipart/form-data">
      <img src="uploaded_img/<?= htmlspecialchars($fetch_profile['image']) ?>" alt="">
      <div class="flex">
         <div class="inputBox">
            <span>Nombre de usuario:</span>
            <input type="text" name="name" value="<?= htmlspecialchars($fetch_profile['name']) ?>" placeholder="Actualizar nombre de usuario" required class="box">
            <span>Email:</span>
            <input type="email" name="email" value="<?= htmlspecialchars($fetch_profile['email']) ?>" placeholder="Actualizar email" required class="box">
            <span>Actualizar imagen:</span>
            <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box">
            <input type="hidden" name="old_image" value="<?= htmlspecialchars($fetch_profile['image']) ?>">
         </div>
         <div class="inputBox">
            <input type="hidden" name="old_pass" value="<?= htmlspecialchars($fetch_profile['password']) ?>">
            <span>Antigua contraseña:</span>
            <input type="password" name="update_pass" placeholder="Ingrese su contraseña anterior" class="box">
            <span>Nueva contraseña:</span>
            <input type="password" name="new_pass" placeholder="Ingrese su nueva contraseña" class="box">
            <span>Confirmar contraseña:</span>
            <input type="password" name="confirm_pass" placeholder="Confirmar nueva contraseña" class="box">
         </div>
      </div>
      <div class="flex-btn">
         <input type="submit" class="btn" value="Actualizar perfil" name="update_profile">
         <a href="home.php" class="option-btn">Ir atrás</a>
      </div>
   </form>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
</body>
</html>