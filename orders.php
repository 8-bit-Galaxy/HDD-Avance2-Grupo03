<?php
require_once 'config.php';

/** @var \PDO $conn */
if (!($conn instanceof PDO)) {
   die('Error: No se pudo establecer la conexión con la base de datos.');
}

session_start();

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
   header('Location: login.php');
   exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Pedidos realizados</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- CSS personalizado -->
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="placed-orders">

   <h1 class="title">
      <span class="word-red">Pe</span><span class="word-green">di</span><span class="word-blue">dos</span>
      <span class="word-red">Rea</span><span class="word-green">liza</span><span class="word-blue">dos</span>
   </h1>

   <div class="box-container">
   <?php
      try {
         $select_orders = $conn->prepare("SELECT * FROM orders WHERE user_id = ?");
         $select_orders->execute([$user_id]);

         if ($select_orders->rowCount() > 0) {
            while ($order = $select_orders->fetch(PDO::FETCH_ASSOC)) {
               ?>
               <div class="box">
                  <p>Fecha: <span><?= htmlspecialchars($order['placed_on']) ?></span></p>
                  <p>Nombre: <span><?= htmlspecialchars($order['name']) ?></span></p>
                  <p>Número: <span><?= htmlspecialchars($order['number']) ?></span></p>
                  <p>Email: <span><?= htmlspecialchars($order['email']) ?></span></p>
                  <p>Dirección: <span><?= htmlspecialchars($order['address']) ?></span></p>
                  <p>Método de pago: <span><?= htmlspecialchars($order['method']) ?></span></p>
                  <p>Tu pedido: <span><?= htmlspecialchars($order['total_products']) ?></span></p>
                  <p>Precio total: <span>S/.<?= htmlspecialchars($order['total_price']) ?></span></p>
                  <p>Estado de pago: 
                     <span style="color:<?= $order['payment_status'] === 'pending' ? 'red' : 'green' ?>">
                        <?= htmlspecialchars($order['payment_status']) ?>
                     </span>
                  </p>
               </div>
               <?php
            }
         } else {
            echo '<p class="empty">¡Aún no se han realizado pedidos!</p>';
         }
      } catch (PDOException $e) {
         echo '<p class="empty">Error al cargar pedidos: ' . htmlspecialchars($e->getMessage()) . '</p>';
      }
   ?>
   </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>