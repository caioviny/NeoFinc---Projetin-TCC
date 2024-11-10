<?php
// Include the database connection
include("../../config/database/conexao.php");

// Start the session
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

// ID do usuário logado
$userId = $_SESSION['user_id'];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Receive data from the form
  $nome_meta = $_POST['nome'] ?? null;
  $valor_meta = $_POST['valor'] ?? null;
  $data_meta = $_POST['data'] ?? null;
  $usuario_id = $_POST['usuario_id'] ?? null;

  // Convert the goal value to the correct format (remove commas, dots, etc.)
  if ($valor_meta) {
      $valor_meta = str_replace(",", ".", str_replace(".", "", $valor_meta));
  }

  // Check if it's a deposit operation
  if (isset($_POST['valor_deposito']) && isset($_POST['id_meta'])) {
      $id_meta = $_POST['id_meta'];
      $valor_deposito = $_POST['valor_deposito'];

      // Convert the deposit value to the correct format
      if ($valor_deposito) {
          $valor_deposito = str_replace(",", ".", str_replace(".", "", $valor_deposito));
      }

      // Start a transaction in the database
      $conn->begin_transaction();

      try {
          // Update the current value of the goal with the deposit value
          $sql_meta = "UPDATE metas SET valor_atual = valor_atual + ? WHERE id = ? AND usuario_id = ?";
          $stmt_meta = $conn->prepare($sql_meta);
          $stmt_meta->bind_param("dii", $valor_deposito, $id_meta, $usuario_id);
          $stmt_meta->execute();

          // Subtract the value from the transactions table
          $sql_transacoes = "UPDATE transacoes SET valor = valor - ? WHERE usuario_id = ? AND valor >= ?";
          $stmt_transacoes = $conn->prepare($sql_transacoes);
          $stmt_transacoes->bind_param("dii", $valor_deposito, $usuario_id, $valor_deposito);
          $stmt_transacoes->execute();

          // Commit the transaction
          $conn->commit();

          // Redirect to the goals page with a success message
          header("Location: ../conteudos/(7) metas.php?sucesso=deposito");
          exit();
      } catch (Exception $e) {
          // If an error occurs, rollback the transaction
          $conn->rollback();
          echo "Error while making deposit: " . $conn->error;
      }
  }

  // Check if it's a withdrawal operation
  if (isset($_POST['valor_resgate']) && isset($_POST['id_meta'])) {
      $id_meta = $_POST['id_meta'];
      $valor_resgate = $_POST['valor_resgate'];

      // Convert the withdrawal value to the correct format
      if ($valor_resgate) {
          $valor_resgate = str_replace(",", ".", str_replace(".", "", $valor_resgate));
      }

      // Start a transaction in the database
      $conn->begin_transaction();

      try {
          // Update the current value of the goal with the withdrawal value
          $sql_meta = "UPDATE metas SET valor_atual = valor_atual - ? WHERE id = ? AND usuario_id = ?";
          $stmt_meta = $conn->prepare($sql_meta);
          $stmt_meta->bind_param("dii", $valor_resgate, $id_meta, $usuario_id);
          $stmt_meta->execute();

          // Register the transaction in the transactions table
          $sql_transacoes = "INSERT INTO transacoes (usuario_id, meta_id, valor, tipo) VALUES (?, ?, ?, 'resgate')";
          $stmt_transacoes = $conn->prepare($sql_transacoes);
          $stmt_transacoes->bind_param("iid", $usuario_id, $id_meta, $valor_resgate);
          $stmt_transacoes->execute();

          // Commit the transaction
          $conn->commit();

          // Redirect to the goals page with a success message
          header("Location: ../conteudos/(7) metas.php?sucesso=resgate");
          exit();
      } catch (Exception $e) {
          // If an error occurs, rollback the transaction
          $conn->rollback();
          echo "Error while making withdrawal: " . $e->getMessage();
      }
  }

  // Validate the fields to add a new goal
  if (!empty($nome_meta) && is_numeric($valor_meta) && !empty($data_meta)) {
    // Prepare the SQL query to insert into the correct column (valor_alvo)
    $sql = "INSERT INTO metas (nome_meta, valor_alvo, data_limite, usuario_id) VALUES (?, ?, ?, ?)";
    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nome_meta, $valor_meta, $data_meta, $usuario_id);

    // Execute the query
    if ($stmt->execute()) {
      // Redirect to the goals page or display a success message
      header("Location: ../conteudos/(7) metas.php?sucesso=1");
      echo '<script>window.location.reload();</script>';
exit();

    } else {
      echo "Error while adding goal: " . $conn->error;
    }
  } else {
    echo "All fields are required and the value must be numeric!";
  }

  // Check if it's a withdrawal operation
  if (isset($_POST['valor_resgatar']) && isset($_POST['id_meta'])) {
    $id_meta = $_POST['id_meta'];
    $valor_resgatar = $_POST['valor_resgatar'];

    // Convert the withdrawal value to the correct format
    if ($valor_resgatar) {
      $valor_resgatar = str_replace(",", ".", str_replace(".", "", $valor_resgatar));
    }

    // Update the current value of the goal subtracting the withdrawn value
    $sql = "UPDATE metas SET valor_atual = valor_atual - ? WHERE id = ? AND usuario_id = ? AND valor_atual >= ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("diii", $valor_resgatar, $id_meta, $usuario_id, $valor_resgatar);

    if ($stmt->execute()) {
      // Redirect to the goals page with a success message
      header("Location: ../conteudos/(7) metas.php?sucesso=resgatar");
      exit();
    } else {
      echo "Error while withdrawing value: " . $conn->error;
    }
  }
}

// Buscar as metas do usuário logado
$sql = "SELECT id, nome_meta, valor_alvo, valor_atual, data_limite FROM metas WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$buscar_metas = $stmt->get_result();


include("../../config/conteudos/metas/apagar_meta.php");
include("../../config/conteudos/metas/navegacao.php");
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Metas</title>
  <link rel="stylesheet" href="../../css/conteudos/metas/metas.css">
  <link rel="stylesheet" href="../../css/conteudos/metas/popUpMetas.css">
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>

  <div class="container">

    <!-- Side Menu -->
    <div class="menu-lateral" onclick="abrirModalAdicionar()">
      <div class="adicionar--btn">
        <img src="../../assets/icons/add--icon.svg" alt="add--btn">
      </div>
    </div>

    <!-- ADD GOAL POPUP -->
    <div class="pop-up-adicionar-container" id="pop-up-adicionar-container" style="display: none;">
      <div class="pop-up-adicionar-conteudo">
        <span class="popup-adicionar-close-btn" id="btn-fechar-popup-adicionar">&times;</span>
        <h2 class="adicionar-titulo">Adicionar Meta</h2>

        <!-- Form to add a goal -->
        <form method="POST" action="">
          <label for="nome_meta">Nome da Meta:</label>
          <input type="text" id="nome_meta" name="nome" required placeholder="Digite o nome da meta">

          <label for="valor_meta">Valor da Meta:</label>
          <input type="text" id="valor_meta" name="valor" required placeholder="0,00">

          <label for="data_meta">Data Limite:</label>
          <input type="date" id="data_meta" name="data" required>

          <input type="hidden" name="usuario_id" value="<?php echo $userId; ?>"> <!-- Usuário logado -->

          <button type="submit">Adicionar Meta</button>
        </form>
      </div>
    </div>

    <!-- Goal Cards -->
    <div class="container-cards">

      <?php while ($meta = $buscar_metas->fetch_assoc()) { ?>
        <div class="card-meta">
          <div class="titulo-card">
            <span><?php echo htmlspecialchars($meta['nome_meta']); ?></span>
            <!-- Form to remove the goal -->
            <form method="POST" action="../conteudos/(7) metas.php" style="display:inline;">
              <input type="hidden" name="id_meta" value="<?php echo $meta['id']; ?>">
              <button type="submit" class="icone-lixeira" aria-label="Remover meta"
                onclick="return confirm('Tem certeza que deseja remover esta meta?');">
                <i class="fi fi-sr-trash"></i>
              </button>
            </form>
          </div>

          <!-- PROGRESS -->
          <?php
          // Check if the current value is greater than the target value and lock it to the target value if necessary
          if ($meta['valor_atual'] >= $meta['valor_alvo']) {
            $valor_atual_exibido = $meta['valor_alvo'];
            $mensagem = "Congratulations, your goal " . $meta['nome_meta'] . " of R$ " . number_format($meta['valor_alvo'], 2, ',', '.') . " has been achieved!";
          } else {
            $valor_atual_exibido = $meta['valor_atual'];
            $mensagem = "";
          }
          ?>

          <div class="progresso-meta">
            <span>R$ <?php echo number_format($valor_atual_exibido, 2, ',', '.'); ?></span> <!-- Current value -->
            <div class="barra-progresso">
              <div class="barra-progresso-preenchida" style="width: <?php
              $progresso = min(($meta['valor_atual'] / $meta['valor_alvo']) * 100, 100);
              echo round($progresso, 2);
              ?>%;"></div>
              <!-- Progress -->
            </div>

            <span>of R$ <?php echo number_format($meta['valor_alvo'], 2, ',', '.'); ?></span>

            <!-- Display the congratulations message if the goal has been achieved -->
            <?php if ($mensagem) { ?>
              <div class="mensagem-meta-alcancada">
                <strong><?php echo $mensagem; ?></strong>
              </div>
            <?php } ?>
          </div>

          <div class="data-limite">
            <span>Prazo para Meta: <?php echo date('d/m/Y', strtotime($meta['data_limite'])); ?></span>
          </div>
          <div class="botoes-meta">
            <!-- Deposit Button -->
            <button class="btn-depositar" onclick="abrirModalDepositar(<?php echo $meta['id']; ?>)">
              <div for="icon2"><img src="../../assets/icons/icon--resgatar--metas.svg" alt="depositar"></div>
              Depositar
            </button>
            <!-- Withdraw Button -->
            <button class="btn-resgatar" onclick="abrirModalResgatar(<?php echo $meta['id']; ?>)">
              <div for="icon2"><img src="../../assets/icons/icon--depositar--meta.svg" alt="resgatar"></div> Resgatar
            </button>

          </div>
          <!-- Element for the chart -->

          <div class="grafico" id="chart-<?php echo $meta['id']; ?>" style="height: 200px; width: 100%;"></div>
        </div>
      <?php } ?>
    </div>

    <!-- Navigation -->
    <div class="navegacao">
      <?php if ($prevOffset !== null) { ?>
        <a href="?offset=<?php echo $prevOffset; ?>" class="setinha">← Anterior</a>
      <?php } ?>
      <?php if ($nextOffset !== null) { ?>
        <a href="?offset=<?php echo $nextOffset; ?>" class="setinha">Próximo →</a>
      <?php } ?>
    </div>

    <!-- DEPOSIT POPUP -->
    <div class="pop-up-depositar-container" id="pop-up-depositar-container" style="display: none;">
      <div class="pop-up-depositar-conteudo">
        <span class="popup-depositar-close-btn" id="btn-fechar-popup-depositar">&times;</span>
        <h2 ss="depositar-titulo">Depositar Valor</h2>

        <!-- Form to deposit -->
        <form method="POST" action="" id="form-depositar">
          <label for="valor_deposito">Valor a Depositar:</label>
          <input type="text" id="valor_deposito" name="valor_deposito" required placeholder="0,00">

          <input type="hidden" name="usuario_id" value="<?php echo $userId; ?>"> <!-- Usuário logado -->
          <input type="hidden" name="id_meta" id="id_meta_depositar" value="">

          <button type="submit">Depositar</button>
        </form>
      </div>
    </div>

    <!-- WITHDRAW POPUP -->
    <div class="pop-up-resgatar-container" id="pop-up-resgatar-container" style="display: none;">
      <div class="pop-up-resgatar-conteudo">
        <span class="popup-resgatar-close-btn" id="btn-fechar-popup-resgatar">&times;</span>
        <h2 class="resgatar-titulo">Resgatar Valor</h2>

        <!-- Form to withdraw -->
        <form method="POST" action="" id="form-resgatar">
          <label for="valor_resgatar">Valor a Resgatar:</label>
          <input type="text" id="valor_resgatar" name="valor_resgatar" required placeholder="0,00">

          <input type="hidden" name="usuario_id" value="<?php echo $userId; ?>"> <!-- Usuário logado -->
          <input type="hidden" name="id_meta" id="id_meta_resgatar" value="">

          <button type="submit">Resgatar</button>
        </form>
      </div>
    </div>

    <script>
      function abrirModalHistorico(metaId) {
        // Show the history popup
        document.getElementById('pop-up-historico-container').style.display = 'block';

        // Show the history of the specific goal
        var historicoLista = document.getElementById('historico-lista');
        historicoLista.innerHTML = '';
        var historico = <?php echo json_encode($historicoDepositos); ?>;
        if (historico[metaId]) {
          historico[metaId].forEach(function(deposito) {
            var listItem = document.createElement('li');
            listItem.textContent = 'R$ ' + deposito.valor.toFixed(2) + ' em ' + new Date(deposito.criada_em).toLocaleDateString();
            historicoLista.appendChild(listItem);
          });
        } else {
          historicoLista.innerHTML = '<li>Nenhum depósito registrado.</li>';
        }
      }

      // Close the history popup
      document.getElementById('btn-fechar-popup-historico').addEventListener('click', function() {
        document.getElementById('pop-up-historico-container').style.display = 'none';
      });
    </script>

    <!-- Charts in script-->
    <?php include("../../config/conteudos/metas/graficos.php")?>

  </div>
  <script src="../../js/conteudos/metas/abrirModais.js"></script>
  <script src="../../js/conteudos/metas/dataAtual.js"></script>
  <script src="../../js/conteudos/metas/formataValor.js"></script>
</body>

</html>


<?php
// Include the database connection
include("../../config/database/conexao.php");

// Start the session
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

// ID do usuário logado
$userId = $_SESSION['user_id'];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Receive data from the form
  $nome_meta = $_POST['nome'] ?? null;
  $valor_meta = $_POST['valor'] ?? null;
  $data_meta = $_POST['data'] ?? null;
  $usuario_id = $_POST['usuario_id'] ?? null;

  // Convert the goal value to the correct format (remove commas, dots, etc.)
  if ($valor_meta) {
      $valor_meta = str_replace(",", ".", str_replace(".", "", $valor_meta));
  }

  // Check if it's a deposit operation
  if (isset($_POST['valor_deposito']) && isset($_POST['id_meta'])) {
      $id_meta = $_POST['id_meta'];
      $valor_deposito = $_POST['valor_deposito'];

      // Convert the deposit value to the correct format
      if ($valor_deposito) {
          $valor_deposito = str_replace(",", ".", str_replace(".", "", $valor_deposito));
      }

      // Start a transaction in the database
      $conn->begin_transaction();

      try {
          // Update the current value of the goal with the deposit value
          $sql_meta = "UPDATE metas SET valor_atual = valor_atual + ? WHERE id = ? AND usuario_id = ?";
          $stmt_meta = $conn->prepare($sql_meta);
          $stmt_meta->bind_param("dii", $valor_deposito, $id_meta, $usuario_id);
          $stmt_meta->execute();

          // Subtract the value from the transactions table
          $sql_transacoes = "UPDATE transacoes SET valor = valor - ? WHERE usuario_id = ? AND valor >= ?";
          $stmt_transacoes = $conn->prepare($sql_transacoes);
          $stmt_transacoes->bind_param("dii", $valor_deposito, $usuario_id, $valor_deposito);
          $stmt_transacoes->execute();

          // Commit the transaction
          $conn->commit();

          // Redirect to the goals page with a success message
          header("Location: ../conteudos/(7) metas.php?sucesso=deposito");
          exit();
      } catch (Exception $e) {
          // If an error occurs, rollback the transaction
          $conn->rollback();
          echo "Error while making deposit: " . $conn->error;
      }
  }

  // Validate the fields to add a new goal
  if (!empty($nome_meta) && is_numeric($valor_meta) && !empty($data_meta)) {
    $sql = "INSERT INTO metas (nome_meta, valor_alvo, data_limite, usuario_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nome_meta, $valor_meta, $data_meta, $usuario_id);

    if ($stmt->execute()) {
      header("Location: ../conteudos/(7) metas.php?sucesso=1");
      exit();
    } else {
      echo "Error while adding goal: " . $conn->error;
    }
  } else {
    echo "Todos os campos são obrigatórios e o valor deve ser numérico!";
  }
}

// Buscar as metas do usuário logado
$sql = "SELECT id, nome_meta, valor_alvo, valor_atual, data_limite FROM metas WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$buscar_metas = $stmt->get_result();

include("../../config/conteudos/metas/apagar_meta.php");
include("../../config/conteudos/metas/navegacao.php");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Metas</title>
  <link rel="stylesheet" href="../../css/conteudos/metas/metas.css">
  <link rel="stylesheet" href="../../css/conteudos/metas/popUpMetas.css">
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>

  <div class="container">
    <div class="menu-lateral" onclick="abrirModalAdicionar()">
      <div class="adicionar--btn">
        <img src="../../assets/icons/add--icon.svg" alt="add--btn">
      </div>
    </div>

    <div class="pop-up-adicionar-container" id="pop-up-adicionar-container" style="display: none;">
      <div class="pop-up-adicionar-conteudo">
        <span class="popup-adicionar-close-btn" id="btn-fechar-popup-adicionar">&times;</span>
        <h2 class="adicionar-titulo">Adicionar Meta</h2>

        <form method="POST" action="">
          <label for="nome_meta">Nome da Meta:</label>
          <input type="text" id="nome_meta" name="nome" required placeholder="Digite o nome da meta">

          <label for="valor_meta">Valor da Meta:</label>
          <input type="text" id="valor_meta" name="valor" required placeholder="0,00">

          <label for="data_meta">Data Limite:</label>
          <input type="date" id="data_meta" name="data" required>

          <input type="hidden" name="usuario_id" value="<?php echo $userId; ?>">

          <button type="submit">Adicionar Meta</button>
        </form>
      </div>
    </div>

    <div class="container-cards">
      <?php while ($meta = $buscar_metas->fetch_assoc()) { ?>
        <div class="card-meta">
          <div class="titulo-card">
            <span><?php echo htmlspecialchars($meta['nome_meta']); ?></span>
            <form method="POST" action="../conteudos/(7) metas.php" style="display:inline;">
              <input type="hidden" name="id_meta" value="<?php echo $meta['id']; ?>">
              <button type="submit" class="icone-lixeira" aria-label="Remover meta"
                onclick="return confirm('Tem certeza que deseja remover esta meta?');">
                <i class="fi fi-sr-trash"></i>
              </button>
            </form>
          </div>

          <?php
          if ($meta['valor_atual'] >= $meta['valor_alvo']) {
            $valor_atual_exibido = $meta['valor_alvo'];
            $mensagem = "Parabéns, sua meta " . $meta['nome_meta'] . " de R$ " . number_format($meta['valor_alvo'], 2, ',', '.') . " foi alcançada!";
          } else {
            $valor_atual_exibido = $meta['valor_atual'];
            $mensagem = "";
          }
          ?>

          <div class="progresso-meta">
            <span>R$ <?php echo number_format($valor_atual_exibido, 2, ',', '.'); ?></span>
            <div class="barra-progresso">
              <div class="barra-progresso-preenchida" style="width: <?php
              $progresso = min(($meta['valor_atual'] / $meta['valor_alvo']) * 100, 100);
              echo round($progresso, 2);
              ?>%;"></div>
            </div>

            <span>de R$ <?php echo number_format($meta['valor_alvo'], 2, ',', '.'); ?></span>

            <?php if ($mensagem) { ?>
              <div class="mensagem-meta-alcancada">
                <strong><?php echo $mensagem; ?></strong>
              </div>
            <?php } ?>
          </div>
        </div>
        
      <?php } ?>
    </div>
  </div>

</body>
</html>

          