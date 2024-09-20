<?php include '../config/database.php' ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Document</title>
</head>
<body>
    <?php include '../views/home.php' ?>
   <div class="conteudo" id="conteudo">
      <!-- Conteúdo da página -->
      <iframe id="mainIframe" src="../conteudos/dashboard.php" width="100%" height="100%"></iframe>
      <button class="toggle-button">></button>
    </div>
</body>
</html>
