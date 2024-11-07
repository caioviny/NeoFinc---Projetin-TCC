<?php
// Incluir a conexão com o banco de dados
include("../../config/database/conexao.php");

// Iniciar a sessão
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

// ID do usuário logado
$userId = $_SESSION['user_id'];

// Função para converter valor para o formato correto
function converterValor($valor) {
    return str_replace(",", ".", str_replace(".", "", $valor));
}

// Função para realizar uma transação
function realizarTransacao($conn, $sql, $params, $successMessage, $errorMessage) {
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(...$params);
        $stmt->execute();
        $conn->commit();
        header("Location: ../conteudos/(7) metas.php?sucesso=" . $successMessage);
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo $errorMessage . $e->getMessage();
    }
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber dados do formulário
    $nome_meta = $_POST['nome'] ?? null;
    $valor_meta = $_POST['valor'] ?? null;
    $data_meta = $_POST['data'] ?? null;

    // Converter valor da meta para o formato correto
    if ($valor_meta) {
        $valor_meta = converterValor($valor_meta);
    }

    // Verificar se é uma operação de depósito
    if (isset($_POST['valor_deposito']) && isset($_POST['id_meta'])) {
        $id_meta = $_POST['id_meta'];
        $valor_deposito = converterValor($_POST['valor_deposito']);

        $sql_meta = "UPDATE metas SET valor_atual = valor_atual + ? WHERE id = ? AND usuario_id = ?";
        $params_meta = ["dii", $valor_deposito, $id_meta, $userId];

        $sql_transacoes = "UPDATE transacoes SET valor = valor - ? WHERE usuario_id = ? AND valor >= ?";
        $params_transacoes = ["dii", $valor_deposito, $userId, $valor_deposito];

        realizarTransacao($conn, $sql_meta, $params_meta, "deposito", "Erro ao realizar depósito: ");
        realizarTransacao($conn, $sql_transacoes, $params_transacoes, "deposito", "Erro ao atualizar transações: ");
    }

    // Verificar se é uma operação de resgate
    if (isset($_POST['valor_resgate']) && isset($_POST['id_meta'])) {
        $id_meta = $_POST['id_meta'];
        $valor_resgate = converterValor($_POST['valor_resgate']);

        $sql_meta = "UPDATE metas SET valor_atual = valor_atual - ? WHERE id = ? AND usuario_id = ?";
        $params_meta = ["dii", $valor_resgate, $id_meta, $userId];

        $sql_transacoes = "INSERT INTO transacoes (usuario_id, meta_id, valor, tipo) VALUES (?, ?, ?, 'resgate')";
        $params_transacoes = ["iid", $userId, $id_meta, $valor_resgate];

        realizarTransacao($conn, $sql_meta, $params_meta, "resgate", "Erro ao realizar resgate: ");
        realizarTransacao($conn, $sql_transacoes, $params_transacoes, "resgate", "Erro ao registrar transação: ");
    }

    // Validar os campos para adicionar uma nova meta
    if (!empty($nome_meta) && is_numeric($valor_meta) && !empty($data_meta)) {
        // Verificar se a meta já existe para o usuário
        $sql_verificar = "SELECT COUNT(*) FROM metas WHERE usuario_id = ? AND nome_meta = ?";
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->bind_param("is", $userId, $nome_meta);
        $stmt_verificar->execute();
        $result_verificar = $stmt_verificar->get_result();
        $count = $result_verificar->fetch_row()[0];

        if ($count > 0) {
            echo "Já existe uma meta com esse nome para este usuário.";
        } else {
            $sql = "INSERT INTO metas (nome_meta, valor_alvo, data_limite, usuario_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $nome_meta, $valor_meta, $data_meta, $userId);

            if ($stmt->execute()) {
                header("Location: ../conteudos/(7) metas.php?sucesso=1");
                exit();
            } else {
                echo "Erro ao adicionar meta: " . $conn->error;
            }
        }
    } else {
        echo "Todos os campos são obrigatórios e o valor deve ser numérico!";
    }

    // Verificar se é uma operação de resgatar
    if (isset($_POST['valor_resgatar']) && isset($_POST['id_meta'])) {
        $id_meta = $_POST['id_meta'];
        $valor_resgatar = converterValor($_POST['valor_resgatar']);

        $sql = "UPDATE metas SET valor_atual = valor_atual - ? WHERE id = ? AND usuario_id = ? AND valor_atual >= ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("diii", $valor_resgatar, $id_meta, $userId, $valor_resgatar);

        if ($stmt->execute()) {
            header("Location: ../conteudos/(7) metas.php?sucesso=resgatar");
            exit();
        } else {
            echo "Erro ao resgatar valor: " . $conn->error;
        }
    }
}

// Buscar as metas do usuário logado
$sql = "SELECT id, nome_meta, valor_alvo, valor_atual, data_limite FROM metas WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

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

    <!-- Menu Lateral -->
    <div class="menu-lateral" onclick="abrirModalAdicionar()">
      <div class="adicionar--btn">
        <img src="../../assets/icons/add--icon.svg" alt="add--btn">
      </div>
    </div>

    <!-- POPUP ADICIONAR META -->
    <div class="pop-up-adicionar-container" id="pop-up-adicionar-container" style="display: none;">
      <div class="pop-up-adicionar-conteudo">
        <span class="popup-adicionar-close-btn" id="btn-fechar-popup-adicionar">&times;</span>
        <h2 class="adicionar-titulo">Adicionar Meta</h2>

        <!-- Formulário para adicionar uma meta -->
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

    <!-- Cards de Metas -->
    <div class="container-cards">

      <?php while ($meta = $result->fetch_assoc()) { ?>
        <div class="card-meta">
          <div class="titulo-card">
            <span><?php echo htmlspecialchars($meta['nome_meta']); ?></span>
            <!-- Formulário para remover a meta -->
            <form method="POST" action="../conteudos/(7) metas.php" style="display:inline;">
              <input type="hidden" name="id_meta" value="<?php echo $meta['id']; ?>">
              <button type="submit" class="icone-lixeira" aria-label="Remover meta"
                onclick="return confirm('Tem certeza que deseja remover esta meta?');">
                <i class="fi fi-sr-trash"></i>
              </button>
            </form>
          </div>

          <!-- PROGRESSO -->
          <?php
          // Verifica se o valor atual é maior que o valor alvo e trava no valor alvo se necessário
          if ($meta['valor_atual'] >= $meta['valor_alvo']) {
            $valor_atual_exibido = $meta['valor_alvo'];
            $mensagem = "Parabéns, sua meta " . $meta['nome_meta'] . " de R$ " . number_format($meta['valor_alvo'], 2, ',', '.') . " foi alcançada!";
          } else {
            $valor_atual_exibido = $meta['valor_atual'];
            $mensagem = "";
          }
          ?>

          <div class="progresso-meta">
            <span>R$ <?php echo number_format($valor_atual_exibido, 2, ',', '.'); ?></span> <!-- Valor atual -->
            <div class="barra-progresso">
              <div class="barra-progresso-preenchida" style="width: <?php
              $progresso = min(($meta['valor_atual'] / $meta['valor_alvo']) * 100, 100);
              echo round($progresso, 2);
              ?>%;"></div>
              <!-- Progresso -->
            </div>

            <span>de R$ <?php echo number_format($meta['valor_alvo'], 2, ',', '.'); ?></span>

            <!-- Exibe a mensagem de parabéns se a meta foi alcançada -->
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
            <!-- Botão Depositar -->
            <button class="btn-depositar" onclick="abrirModalDepositar(<?php echo $meta['id']; ?>)">
              <div for="icon2"><img src="../../assets/icons/icon--resgatar--metas.svg" alt="depositar"></div>
              Depositar
            </button>
            <!-- Botão Resgatar -->
            <button class="btn-resgatar" onclick="abrirModalResgatar(<?php echo $meta['id']; ?>)">
              <div for="icon2"><img src="../../assets/icons/icon--depositar--meta.svg" alt="resgatar"></div> Resgatar
            </button>

            <!--<button class="btn-historico" onclick="abrirModalHistorico(<?php echo $meta['id']; ?>)">
              <div for="icon2"><img src="../../assets/icons/icon--historico--metas.svg" alt=""></div> Histórico
            </button>-->

          </div>
          <!-- Elemento para o gráfico -->

          <div class="grafico" id="chart-<?php echo $meta['id']; ?>" style="height: 200px; width: 100%;"></div>
        </div>
      <?php } ?>
    </div>

    <!-- Navegação -->
    <div class="navegacao">
      <?php if ($prevOffset !== null) { ?>
        <a href="?offset=<?php echo $prevOffset; ?>" class="setinha">← Anterior</a>
      <?php } ?>
      <?php if ($nextOffset !== null) { ?>
        <a href="?offset=<?php echo $nextOffset; ?>" class="setinha">Próximo →</a>
      <?php } ?>
    </div>

    <!-- POPUP DEPOSITAR -->
    <div class="pop-up-depositar-container" id="pop-up-depositar-container" style="display: none;">
      <div class="pop-up-depositar-conteudo">
        <span class="popup-depositar-close-btn" id="btn-fechar-popup-depositar">&times;</span>
        <h2 class="depositar-titulo">Depositar Valor</h2>

        <!-- Formulário para depósito -->
        <form method="POST" action="" id="form-depositar">
          <label for="valor_deposito">Valor a Depositar:</label>
          <input type="text" id="valor_deposito" name="valor_deposito" required placeholder="0,00">

          <input type="hidden" name="usuario_id" value="<?php echo $userId; ?>"> <!-- Usuário logado -->
          <input type="hidden" name="id_meta" id="id_meta_depositar" value="">

          <button type="submit">Depositar</button>
        </form>
      </div>
    </div>

    <!-- POPUP RESGATAR -->
    <div class="pop-up-resgatar-container" id="pop-up-resgatar-container" style="display: none;">
      <div class="pop-up-resgatar-conteudo">
        <span class="popup-resgatar-close-btn" id="btn-fechar-popup-resgatar">&times;</span>
        <h2 class="resgatar-titulo">Resgatar Valor</h2>

        <!-- Formulário para resgatar -->
        <form method="POST" action="" id="form-resgatar">
          <label for="valor_resgatar">Valor a Resgatar:</label>
          <input type="text" id="valor_resgatar" name="valor_resgatar" required placeholder="0,00">

          <input type="hidden" name="usuario_id" value="<?php echo $userId; ?>"> <!-- Usuário logado -->
          <input type="hidden" name="id_meta" id="id_meta_resgatar" value="">

          <button type="submit">Resgatar</button>
        </form>
      </div>
    </div>

    <!-- Gráficos em script-->
    <?php include("../../config/conteudos/metas/graficos.php")?>

  </div>
  <script src="../../js/conteudos/metas/abrirModais.js"></script>
  <script src="../../js/conteudos/metas/dataAtual.js"></script>
  <script src="../../js/conteudos/metas/formataValor.js"></script>
</body>

</html>
