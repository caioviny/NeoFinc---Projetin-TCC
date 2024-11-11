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
            $stmt_meta->bind_param("dii", $valor_deposito, $id_meta, $userId); // Use $userId to ensure it belongs to the logged-in user
            $stmt_meta->execute();

            // Subtract the value from the transactions table
            $sql_transacoes = "UPDATE transacoes SET valor = valor - ? WHERE usuario_id = ? AND valor >= ?";
            $stmt_transacoes = $conn->prepare($sql_transacoes);
            $stmt_transacoes->bind_param("dii", $valor_deposito, $userId, $valor_deposito); // Use $userId
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
            $stmt_meta->bind_param("dii", $valor_resgate, $id_meta, $userId); // Use $userId
            $stmt_meta->execute();

            // Register the transaction in the transactions table
            $sql_transacoes = "INSERT INTO transacoes (usuario_id, meta_id, valor, tipo) VALUES (?, ?, ?, 'resgate')";
            $stmt_transacoes = $conn->prepare($sql_transacoes);
            $stmt_transacoes->bind_param("iid", $userId, $id_meta, $valor_resgate); // Use $userId
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
        $sql = "INSERT INTO metas (nome_meta, valor_alvo, data_limite, usuario_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nome_meta, $valor_meta, $data_meta, $userId); // Use $userId instead of user input

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
$stmt->bind_param("i", $userId); // Use $userId to fetch only the logged-in user's goals
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
          <!-- Elemento do gráfico -->
          <div class="grafico" id="chart-<?php echo $meta['id']; ?>"></div>
        </div>
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

    <!-- Script do ApexCharts para os gráficos -->
    <script>
      <?php while ($meta = $buscar_metas->fetch_assoc()) { ?>
        var options = {
          chart: {
            type: 'radialBar',
            height: 150,
          },
          series: [<?php echo round($progresso, 2); ?>],
          labels: ['Progresso'],
          colors: ['#00e060']
        };
        var chart = new ApexCharts(document.querySelector("#chart-<?php echo $meta['id']; ?>"), options);
        chart.render();
      <?php } ?>
    </script>
  </div>

  <script src="../../js/conteudos/metas/abrirModais.js"></script>
  <script src="../../js/conteudos/metas/dataAtual.js"></script>
  <script src="../../js/conteudos/metas/formataValor.js"></script>
</body>
</html>
