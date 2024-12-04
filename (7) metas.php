<?php
// Conexão do banco de dados
require_once '../../config/database/conexao.php';
session_start();

// Verificando sessão
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
  echo "Erro: Usuário não autenticado.";
  exit();
}

$userId = $_SESSION['user_id']; // Puxando ID do user

// Verificar se o usuário existe na tabela 'users'
$sql_check_user = "SELECT id FROM users WHERE id = ?";
$stmt_check_user = $conn->prepare($sql_check_user);
$stmt_check_user->bind_param("i", $userId);
$stmt_check_user->execute();
$stmt_check_user->store_result();

// Se não encontrar o usuário, interrompa a execução
if ($stmt_check_user->num_rows == 0) {
  echo "Erro: O usuário não existe.";
  exit();
}
$stmt_check_user->close();

// Consulta para obter as metas do usuário
$sql_metas = "SELECT id_meta, nome_meta, valor_alvo, valor_atual, prazo FROM metas_usuario WHERE id_usuario = ?";
$stmt_metas = $conn->prepare($sql_metas);
$stmt_metas->bind_param("i", $userId);
$stmt_metas->execute();
$result = $stmt_metas->get_result();

// Controllers de metas
include('../../config/conteudos/metas/adicionar_meta.php');
include('../../config/conteudos/metas/logica_metas.php');
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Minhas Metas - Neo Finance</title>
  <link rel="stylesheet" href="../../css/conteudos/metas/metas.css">
  <link rel="stylesheet" href="../../css/conteudos/metas/popUpMetas.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.4.0/dist/confetti.browser.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>
  <div class="container">
    <div class="titulo-metas">
      <h1>Metas<img src="../../assets/icons/home--sidebar/metas--icon.svg" alt=""></h1>
    </div>

    <div class="menu-criar-meta" id="menuCriarMeta">
      <!-- Formulário para criação de meta -->
      <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <span class="close-btn">&times;</span>
        <label for="goal_name">Nome da Meta:</label>
        <input type="text" id="goal_name" name="goal_name" required><br><br>
        <label for="target_amount">Valor Alvo:</label>
        <input type="number" id="target_amount" name="target_amount" required><br><br>
        <label for="deadline">Prazo:</label>
        <input type="date" id="deadline" name="deadline" required><br><br>
        <button type="submit">Criar Meta</button>
      </form>
    </div>

    <div class="container-cards">
      <div class="navigation-buttons">
        <button id="prevBtn">&lt;</button>
        <button id="nextBtn">&gt;</button>
      </div>

      <div class="container--add--metas">
        <!-- Botão de Adicionar -->
        <div class="adicionar--btn" id="adicionarBtn">
          <img src="../../assets/icons/add--icon.svg" alt="add--btn">
        </div>
      </div>

      <div class="card-container">
        <?php while ($row = $result->fetch_assoc()):
          $goalId = $row['id_meta'];  // Defina o ID da meta para o histórico
          $deadline = date('d/m/Y', strtotime($row['prazo'])); // Formata a data de prazo
          ?>
          <div class="card-meta" data-id="<?php echo $goalId; ?>">
            <div class="titulo-card">
              <!-- Ícone de exclusão -->
              <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display:inline;">
                <input type="hidden" name="delete_goal_id" value="<?php echo $goalId; ?>">
                <button type="submit" style="background: none; border: none; color: red; cursor: pointer;">
                  <i class="fas fa-trash"></i> Excluir
                </button>
              </form>
              <span><?php echo $row['nome_meta']; ?></span>
            </div>
            <div class="progresso-meta">
              <span>Progresso:</span>
              <div class="barra-progresso">
                <div class="barra-progresso-preenchida"
                  style="width: <?php echo ($row['valor_atual'] / $row['valor_alvo']) * 100; ?>%;"></div>
              </div>
              <span>R$ <?php echo $row['valor_atual']; ?> de R$ <?php echo $row['valor_alvo']; ?></span>
              <?php if ($row['valor_atual'] >= $row['valor_alvo']): ?>
                <div class="mensagem-meta-alcancada">Meta Alcançada!</div>
                <?php
                // Verificar se a meta já foi celebrada
                if (!isset($_SESSION['celebrated_goals']) || !in_array($goalId, $_SESSION['celebrated_goals'])):
                  // Marcar a meta como celebrada
                  $_SESSION['celebrated_goals'][] = $goalId;
                  ?>
                  <script>
                    document.addEventListener('DOMContentLoaded', function () {
                      var end = Date.now() + (8 * 1000);
                      var colors = ['#bb0000', '#ffffff'];
                      (function frame() {
                        confetti({
                          particleCount: 2,
                          angle: 60,
                          spread: 55,
                          origin: { x: 0 }
                        });
                        confetti({
                          particleCount: 2,
                          angle: 120,
                          spread: 55,
                          origin: { x: 1 }
                        });
                        if (Date.now() < end) {
                          requestAnimationFrame(frame);
                        }
                      }());

                      // Exibir mensagem de parabéns
                      var congratulationsMessage = document.createElement('div');
                      congratulationsMessage.className = 'congratulations-message';
                      congratulationsMessage.innerHTML = 'Parabéns! Você alcançou sua meta de <?php echo $row['nome_meta']; ?>!';
                      document.body.appendChild(congratulationsMessage);

                      setTimeout(function () {
                        congratulationsMessage.style.display = 'block';
                      }, 1000);

                      setTimeout(function () {
                        congratulationsMessage.style.display = 'none';
                        document.body.removeChild(congratulationsMessage);
                      }, 8000);

                      // Adicionar emojis animados
                      var emojis = ['🎉', '🎊', '🎈', '🎆', '🎇', '🎂', '🍾', '🥳', '🌟', '💥'];
                      var emojiInterval = setInterval(function () {
                        var emoji = document.createElement('div');
                        emoji.className = 'emoji';
                        emoji.innerHTML = emojis[Math.floor(Math.random() * emojis.length)];
                        emoji.style.left = Math.random() * 100 + 'vw';
                        document.body.appendChild(emoji);

                        setTimeout(function () {
                          document.body.removeChild(emoji);
                        }, 8000);
                      }, 500);

                      setTimeout(function () {
                        clearInterval(emojiInterval);
                      }, 8000);
                    });
                  </script>
                <?php endif; ?>
              <?php endif; ?>
              <h2>Até <?php echo $deadline; ?></h2> <!-- Exibe a data de prazo aqui -->
            </div>

            <!-- Elemento para o gráfico -->
            <div class="grafico" id="chart-<?php echo $goalId; ?>" style="height: 200px; width: 100%;"></div>

            <!-- Botão para selecionar ação -->
            <button class="selectActionBtn">Selecionar Ação</button>

            <!-- Botões de ação -->
            <div class="actionButtons" style="display:none;">
              <button class="actionBtn" data-action="depositar">Depositar<img
                  src="../../assets/icons/icon--depositar--meta.svg" alt=""></button>
              <button class="actionBtn" data-action="resgatar">Resgatar<img
                  src="../../assets/icons/icon--resgatar--metas.svg" alt=""></button>
              <button class="actionBtn" data-action="verHistorico">Ver Histórico<img
                  src="../../assets/icons/icon--historico--metas.svg" alt=""></button>
            </div>

            <div class="container-formularios">
                <!-- Formulário de resgatar -->
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-resgatar" style="display:none;">
              <button type="button" class="back-btn">&lt;</button>
              <input type="hidden" name="goal_id" value="<?php echo $goalId; ?>">
              <label for="withdraw_value">Valor a Resgatar:</label>
              <input type="number" id="withdraw_value" name="withdraw_value" required>
              <button type="submit" name="resgatar" class="btn--resgatar">Resgatar</button>
            </form>

            <!-- Formulário de depósito -->
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-depositar" style="display:none;">
              <button type="button" class="back-btn">&lt;</button>
              <input type="hidden" name="goal_id" value="<?php echo $goalId; ?>">
              <label for="deposit_value">Valor a Depositar:</label>
              <input type="number" id="deposit_value" name="deposit_value" required>
              <button type="submit" name="depositar" class="btn--depositar">Depositar</button>
            </form>

            <!-- Exibição do histórico -->
            <div class="historico-transacoes" style="display:none;">
              <button type="button" class="back-btn">&lt;</button>
              <h3>Histórico</h3>
              <table>
                <tr>
                  <th>Tipo</th>
                  <th>Valor</th>
                  <th>Data</th>
                </tr>
                <?php
                // Consulta de histórico para a meta específica
                $sql_history = "SELECT tipo_transacao, valor, data_transacao FROM historico_transacoes WHERE id_usuario = ? AND id_meta = ?";
                $stmt_history = $conn->prepare($sql_history);
                $stmt_history->bind_param("ii", $userId, $goalId);  // Passando o ID da meta
                $stmt_history->execute();
                $history_result = $stmt_history->get_result();

                while ($history = $history_result->fetch_assoc()):
                  ?>
                  <tr>
                    <td><?php echo ucfirst($history['tipo_transacao']); ?></td>
                    <td>R$ <?php echo number_format($history['valor'], 2, ',', '.'); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($history['data_transacao'])); ?></td>
                  </tr>
                <?php endwhile; ?>
              </table>
            </div>
            </div>

            <!-- Botão para finalizar meta -->
            <?php if ($row['valor_atual'] >= $row['valor_alvo']): ?>
              <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" style="display:inline;">
                <input type="hidden" name="delete_goal_id" value="<?php echo $goalId; ?>">
                <button type="submit" class="finalizar-meta-btn">
                  <i class="fas fa-check"></i> Finalizar Meta
                </button>
              </form>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>

  <script src="../../js/conteudos/metas/abrirModais.js"></script>
  <script src="../../js/conteudos/metas/dataAtual.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const cards = document.querySelectorAll('.card-meta');
      const prevBtn = document.getElementById('prevBtn');
      const nextBtn = document.getElementById('nextBtn');
      let currentIndex = 0;

      function showCard(index) {
        cards.forEach((card, i) => {
          card.classList.toggle('active', i === index);
        });
      }

      prevBtn.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + cards.length) % cards.length;
        showCard(currentIndex);
      });

      nextBtn.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % cards.length;
        showCard(currentIndex);
      });

      // Mostra o primeiro card ao carregar a página
      showCard(currentIndex);

      // Gráfico de progresso das metas
      <?php foreach ($result as $meta) { ?>
        var progresso = Math.min(Math.round((<?php echo ($meta['valor_atual'] / $meta['valor_alvo']) * 100; ?>)), 100); // Limita a 100%

        var chartOptions<?php echo $meta['id_meta']; ?> = {
          series: [progresso], // Passa o valor limitado
          chart: {
            height: 200,
            type: 'radialBar',
            offsetY: 0,
            sparkline: {
              enabled: false
            }
          },
          plotOptions: {
            radialBar: {
              startAngle: -90, // Começa o gráfico no topo
              endAngle: 90, // Termina o gráfico no topo
              hollow: {
                size: '50%',
              },
              track: {
                background: '#f0f0f0', // Fundo da trilha
                strokeWidth: '70%',
              },
              dataLabels: {
                show: true,
                name: {
                  offsetY: -5,
                  color: '#333',
                  fontSize: '14px'
                },
                value: {
                  formatter: function (val) {
                    return Math.min(Math.round(val), 100); // Arredonda e limita o valor a 100%
                  },
                  color: '#28a745', // Cor do valor
                  fontSize: '22px',
                  show: true,
                }
              }
            },
          },
          fill: {
            colors: ['#28a745'], // Cor verde para todos os gráficos
          },
          stroke: {
            lineCap: 'round'
          },
          labels: ['Progresso'],
        };

        var chart<?php echo $meta['id_meta']; ?> = new ApexCharts(document.querySelector("#chart-<?php echo $meta['id_meta']; ?>"), chartOptions<?php echo $meta['id_meta']; ?>);
        chart<?php echo $meta['id_meta']; ?>.render();
      <?php } ?>
    });
  </script>

</body>

</html>
<?php $conn->close(); ?>
