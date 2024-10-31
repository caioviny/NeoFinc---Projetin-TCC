<?php
// Incluir a conexão com o banco de dados
include("../../config/database/conexao.php");

// Iniciar a sessão
session_start();

// Obter o ID do usuário atual (substitua isso com o ID do usuário atual em sua aplicação)
$usuario_id = 1; // Exemplo: ID do usuário atual

// Funções de auxílio
function converterData($data) {
    return DateTime::createFromFormat('d/m/Y', $data)->format('Y-m-d');
}

function converterValor($valor) {
    return str_replace(",", ".", str_replace(".", "", $valor));
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

    // Converter data da meta para o formato correto
    if ($data_meta) {
        $data_meta = converterData($data_meta);
    }

    // Verificar se é uma operação de depósito
    if (isset($_POST['valor_deposito']) && isset($_POST['id_meta'])) {
        $id_meta = $_POST['id_meta'];
        $valor_deposito = converterValor($_POST['valor_deposito']);
        realizarTransacao($conn, $id_meta, $valor_deposito, $usuario_id, 'deposito');
    }

    // Verificar se é uma operação de resgate
    if (isset($_POST['valor_resgate']) && isset($_POST['id_meta'])) {
        $id_meta = $_POST['id_meta'];
        $valor_resgate = converterValor($_POST['valor_resgate']);
        realizarTransacao($conn, $id_meta, $valor_resgate, $usuario_id, 'resgate');
    }

    // Validar os campos para adicionar uma nova meta
    if (!empty($nome_meta) && is_numeric($valor_meta) && !empty($data_meta)) {
        adicionarMeta($conn, $nome_meta, $valor_meta, $data_meta, $usuario_id);
    } else {
        echo "Todos os campos são obrigatórios e o valor deve ser numérico!";
    }

    // Verificar se é uma operação de resgatar
    if (isset($_POST['valor_resgatar']) && isset($_POST['id_meta'])) {
        $id_meta = $_POST['id_meta'];
        $valor_resgatar = converterValor($_POST['valor_resgatar']);
        resgatarValor($conn, $id_meta, $valor_resgatar, $usuario_id);
    }
}

// Função para realizar transações (depósito ou resgate)
function realizarTransacao($conn, $id_meta, $valor, $usuario_id, $tipo) {
    $conn->begin_transaction();
    try {
        if ($tipo === 'deposito') {
            atualizarMeta($conn, $id_meta, $valor, $usuario_id, '+');
            subtrairTransacoes($conn, $valor, $usuario_id);
            $conn->commit();
            header("Location: ../conteudos/(7) metas.php?sucesso=deposito");
        } elseif ($tipo === 'resgate') {
            $valor_atual = verificarValorAtual($conn, $id_meta, $usuario_id);
            if ($valor_atual > 0 && $valor_atual >= $valor) {
                atualizarMeta($conn, $id_meta, $valor, $usuario_id, '-');
                registrarTransacao($conn, $id_meta, $valor, $usuario_id, 'resgate');
                $conn->commit();
                header("Location: ../conteudos/(7) metas.php?sucesso=resgate");
            } else {
                echo "Não é possível resgatar valor da meta, pois o valor atual é zero ou negativo.";
            }
        }
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Erro ao realizar transação: " . $e->getMessage();
    }
}

// Função para adicionar uma nova meta
function adicionarMeta($conn, $nome_meta, $valor_meta, $data_meta, $usuario_id) {
    $sql = "INSERT INTO metas (nome_meta, valor_alvo, data_limite, usuario_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nome_meta, $valor_meta, $data_meta, $usuario_id);

    if ($stmt->execute()) {
        header("Location: ../conteudos/(7) metas.php?sucesso=1");
        exit();
    } else {
        if ($conn->errno == 1062) {
            echo "Erro ao adicionar meta: Já existe uma meta com esse nome para este usuário.";
        } else {
            echo "Erro ao adicionar meta: " . $conn->error;
        }
    }
}

// Função para resgatar valor da meta
function resgatarValor($conn, $id_meta, $valor_resgatar, $usuario_id) {
    $valor_atual = verificarValorAtual($conn, $id_meta, $usuario_id);
    if ($valor_atual > 0 && $valor_atual >= $valor_resgatar) {
        $sql = "UPDATE metas SET valor_atual = valor_atual - ? WHERE id = ? AND usuario_id = ? AND valor_atual >= ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("diii", $valor_resgatar, $id_meta, $usuario_id, $valor_resgatar);

        if ($stmt->execute()) {
            header("Location: ../conteudos/(7) metas.php?sucesso=resgatar");
            exit();
        } else {
            echo "Erro ao resgatar valor: " . $conn->error;
        }
    } else {
        echo "Não é possível resgatar valor da meta, pois o valor atual é zero ou negativo.";
    }
}

// Função para atualizar o valor da meta
function atualizarMeta($conn, $id_meta, $valor, $usuario_id, $operacao) {
    $sql_meta = "UPDATE metas SET valor_atual = valor_atual $operacao ? WHERE id = ? AND usuario_id = ?";
    $stmt_meta = $conn->prepare($sql_meta);
    $stmt_meta->bind_param("dii", $valor, $id_meta, $usuario_id);
    $stmt_meta->execute();
}

// Função para subtrair o valor da tabela transacoes
function subtrairTransacoes($conn, $valor_deposito, $usuario_id) {
    $sql_transacoes = "UPDATE transacoes SET valor = valor - ? WHERE usuario_id = ? AND valor >= ?";
    $stmt_transacoes = $conn->prepare($sql_transacoes);
    $stmt_transacoes->bind_param("dii", $valor_deposito, $usuario_id, $valor_deposito);
    $stmt_transacoes->execute();
}

// Função para registrar a transação na tabela transacoes
function registrarTransacao($conn, $id_meta, $valor_resgate, $usuario_id, $tipo) {
    $sql_transacoes = "INSERT INTO transacoes (usuario_id, meta_id, valor, tipo) VALUES (?, ?, ?, ?)";
    $stmt_transacoes = $conn->prepare($sql_transacoes);
    $stmt_transacoes->bind_param("iids", $usuario_id, $id_meta, $valor_resgate, $tipo);
    $stmt_transacoes->execute();
}

// Função para verificar o valor atual da meta
function verificarValorAtual($conn, $id_meta, $usuario_id) {
    $sql_verificar_valor = "SELECT valor_atual FROM metas WHERE id = ? AND usuario_id = ?";
    $stmt_verificar_valor = $conn->prepare($sql_verificar_valor);
    $stmt_verificar_valor->bind_param("ii", $id_meta, $usuario_id);
    $stmt_verificar_valor->execute();
    $stmt_verificar_valor->bind_result($valor_atual);
    $stmt_verificar_valor->fetch();
    return $valor_atual;
}

// Consulta SQL para obter as metas do usuário atual
$sql = "SELECT * FROM metas WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>

<body>
  <div class="container">
    <!-- Menu Lateral -->
    <div class="menu-lateral" onclick="abrirModalAdicionar()">
      <div class="adicionar--btn">
        <img src="../../assets/icons/icon--add--btn.png" alt="add--btn">
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
          <input type="text" id="data_meta" name="data" required>

          <input type="hidden" name="usuario_id" value="1"> <!-- Exemplo para usuario_id -->

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
          </div>

          <!-- Elemento para o gráfico -->
          <div class="grafico" id="chart-<?php echo $meta['id']; ?>" style="height: 200px; width: 100%;"></div>
        </div>
      <?php } ?>

      <!-- Navegação -->
      <div class="navegacao">
        <?php if ($prevOffset !== null) { ?>
          <a href="?offset=<?php echo $prevOffset; ?>" class="setinha"><span class="setinha">←</span> Anterior</a>
        <?php } ?>
        <?php if ($nextOffset !== null) { ?>
          <a href="?offset=<?php echo $nextOffset; ?>" class="setinha">Próximo <span class="setinha">→</span></a>
        <?php } ?>
      </div>
    </div>

    <!-- POPUP DEPOSITAR -->
    <div class="pop-up-depositar-container" id="pop-up-depositar-container" style="display: none;">
      <div class="pop-up-depositar-conteudo">
        <span class="popup-depositar-close-btn" id="btn-fechar-popup-depositar">&times;</span>
        <h2 ss="depositar-titulo">Depositar Valor</h2>

        <!-- Formulário para depósito -->
        <form method="POST" action="" id="form-depositar">
          <label for="valor_deposito">Valor a Depositar:</label>
          <input type="text" id="valor_deposito" name="valor_deposito" required placeholder="0,00">

          <input type="hidden" name="usuario_id" value="1"> <!-- Exemplo para usuario_id -->
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

          <input type="hidden" name="usuario_id" value="1"> <!-- Exemplo para usuario_id -->
          <input type="hidden" name="id_meta" id="id_meta_resgatar" value="">

          <button type="submit">Resgatar</button>
        </form>
      </div>
    </div>

    <script>
      flatpickr("#data_meta", {
        dateFormat: "d/m/Y", // Formato da data (dia/mês/ano)
        minDate: "today", // Permitir apenas datas a partir de hoje
        locale: {
          firstDayOfWeek: 1, // Começar a semana na segunda-feira
          weekdays: {
            shorthand: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'],
            longhand: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado']
          },
          months: {
            shorthand: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            longhand: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro']
          },
          rangeSeparator: ' até ', // Para seleção de intervalo (se necessário)
          scrollTitle: 'Role para aumentar', // Título de rolagem
          toggleTitle: 'Clique para alternar', // Título de alternância
          amPM: ['AM', 'PM'], // Para formatação de hora
        }
      });
    </script>

    <script>
      document.getElementById('form-resgatar').addEventListener('submit', function(event) {
        var valorResgatar = parseFloat(document.getElementById('valor_resgatar').value.replace(',', '.'));
        var idMeta = document.getElementById('id_meta_resgatar').value;
        var valorAtualMeta = <?php echo json_encode($meta['valor_atual']); ?>;

        if (valorResgatar > valorAtualMeta) {
          alert('O valor a resgatar não pode ser maior que o valor atual da meta.');
          event.preventDefault(); // Impede o envio do formulário
        }
      });
    </script>

    <!-- Gráficos em script-->
    <?php include("../../config/conteudos/metas/graficos.php")?>
  </div>
  <script src="../../js/conteudos/metas/abrirModais.js"></script>
  <script src="../../js/conteudos/metas/dataAtual.js"></script>
  <script src="../../js/conteudos/metas/formataValor.js"></script>
</body>

</html>
