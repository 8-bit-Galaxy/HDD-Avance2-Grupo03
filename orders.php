<?php
// Validación del funcionamiento del login utilizando el parámetro de ci.yml
require_once 'config.php';

if (!($conn instanceof PDO)) {
   die('Error: No se pudo conectar a la base de datos.');
}

session_start();

if (isset($_POST['submit'])) {
   $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
   $pass_raw = $_POST['pass'];
   $pass = filter_var($pass_raw, FILTER_SANITIZE_STRING);
   $pass_md5 = md5($pass); // Nota: Considera usar password_hash en proyectos reales

   try {
      $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
      $stmt = $conn->prepare($sql);
      $stmt->execute([$email, $pass_md5]);

      if ($stmt->rowCount() > 0) {
         $row = $stmt->fetch(PDO::FETCH_ASSOC);

         if ($row['user_type'] === 'admin') {
            $_SESSION['admin_id'] = $row['id'];
            header('Location: admin_page.php');
            exit;
         } elseif ($row['user_type'] === 'user') {
            $_SESSION['user_id'] = $row['id'];
            header('Location: home.php');
            exit;
         } else {
            $message[] = 'Tipo de usuario no reconocido.';
         }
      } else {
         $message[] = '¡Correo o contraseña incorrectos!';
      }
   } catch (PDOException $e) {
      $message[] = 'Error al iniciar sesión: ' . $e->getMessage();
   }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Iniciar sesión</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- CSS personalizado -->
   <link rel="stylesheet" href="css/components.css">
</head>
<body>

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

<section class="form-container">

   <form action="" method="POST">
      <h3>Inicia sesión ahora</h3>
      <input type="email" name="email" class="box" placeholder="Ingresa tu email" required>
      <input type="password" name="pass" class="box" placeholder="Ingresa tu contraseña" required>
      <input type="submit" value="Inicia sesión ahora" class="btn" name="submit">
      <p>¿No tienes una cuenta? <a href="register.php">Regístrate ahora</a></p>
   </form>

</section>

</body>
</html>