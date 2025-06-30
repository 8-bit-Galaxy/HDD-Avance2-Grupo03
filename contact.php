<?php
/** @var \PDO $conn */
/** @var int|string|null $user_id */

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit;
};

if(isset($_POST['send'])){

   $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
   $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
   $msg = filter_var($_POST['msg'], FILTER_SANITIZE_STRING);

   $select_message = $conn->prepare("SELECT * FROM message WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $select_message->execute([$name, $email, $number, $msg]);

   if($select_message->rowCount() > 0){
      $message[] = '¡Mensaje ya enviado!';
   }else{
      $insert_message = $conn->prepare("INSERT INTO message(user_id, name, email, number, message) VALUES(?,?,?,?,?)");
      $insert_message->execute([$user_id, $name, $email, $number, $msg]);
      $message[] = '¡Mensaje enviado exitosamente!';
   }

}
?>

<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contacto</title>

   <!-- Font Awesome CDN -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="contact">

   <h1 class="title">
      <span class="word-red">Po</span><span class="word-green">ner</span><span class="word-blue">se</span>
      <span class="word-red">en</span>
      <span class="word-green">Con</span><span class="word-blue">tac</span><span class="word-red">to</span>
   </h1>

   <form action="" method="POST">
      <input type="text" name="name" class="box" required placeholder="Ingresa tu nombre">
      <input type="email" name="email" class="box" required placeholder="Ingresa tu email">
      <input type="number" name="number" min="0" class="box" required placeholder="Ingresa tu número">
      <textarea name="msg" class="box" required placeholder="Ingresa tu mensaje" cols="30" rows="10"></textarea>
      <input type="submit" value="Enviar mensaje" class="btn" name="send">
   </form>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>