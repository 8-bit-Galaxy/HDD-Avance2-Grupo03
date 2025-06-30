<?php

require_once 'config.php';

/** @var \PDO $conn */
if (!($conn instanceof PDO)) {
   die('Error: No se pudo conectar a la base de datos.');
}

$message = [];

if (isset($_POST['submit'])) {

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

   $pass_raw = $_POST['pass'] ?? '';
   $cpass_raw = $_POST['cpass'] ?? '';

   $pass = filter_var($pass_raw, FILTER_SANITIZE_STRING);
   $cpass = filter_var($cpass_raw, FILTER_SANITIZE_STRING);

   $pass_hash = md5($pass);
   $cpass_hash = md5($cpass);

   $image = $_FILES['image']['name'] ?? '';
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'] ?? 0;
   $image_tmp_name = $_FILES['image']['tmp_name'] ?? '';
   $image_folder = 'uploaded_img/' . $image;

   $select = $conn->prepare("SELECT * FROM users WHERE email = ?");
   $select->execute([$email]);

   if ($select->rowCount() > 0) {
      $message[] = '¡El correo electrónico ya está registrado!';
   } elseif ($pass !== $cpass) {
      $message[] = '¡Las contraseñas no coinciden!';
   } elseif ($image_size > 2000000) {
      $message[] = '¡La imagen es demasiado grande (máx. 2MB)!';
   } else {
      $insert = $conn->prepare("INSERT INTO users(name, email, password, image, user_type) VALUES(?,?,?,?,?)");
      $insert_success = $insert->execute([$name, $email, $pass_hash, $image, 'user']);

      if ($insert_success) {
         move_uploaded_file($image_tmp_name, $image_folder);
         $message[] = '¡Registrado correctamente!';
         header('Location: login.php');
         exit;
      } else {
         $message[] = '¡Error al registrar!';
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
   <title>Registro</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- CSS personalizado -->
   <link rel="stylesheet" href="css/components.css">
</head>
<body>

<?php if (!empty($message)): ?>
   <?php foreach ($message as $msg): ?>
      <div class="message">
         <span><?= htmlspecialchars($msg) ?></span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
   <?php endforeach; ?>
<?php endif; ?>

<section class="form-container">

   <form action="" enctype="multipart/form-data" method="POST">
      <h3>Regístrate ahora</h3>
      <input type="text" name="name" class="box" placeholder="Ingresa tu nombre" required>
      <input type="email" name="email" class="box" placeholder="Ingresa tu email" required>
      <input type="password" name="pass" class="box" placeholder="Ingrese una contraseña" required>
      <input type="password" name="cpass" class="box" placeholder="Confirme la contraseña" required>
      <input type="file" name="image" class="box" required accept="image/jpg, image/jpeg, image/png">
      <input type="submit" value="Regístrate ahora" class="btn" name="submit">
      <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
   </form>

</section>

</body>
</html>