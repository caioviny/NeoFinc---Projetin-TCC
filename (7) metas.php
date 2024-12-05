<?php
require_once '../../config/database/conexao.php';
session_start();

if (empty($_SESSION['user_id'])) {
    echo "Erro: Usuário não autenticado.";
    exit();
}

$userId = $_SESSION['user_id'];

$sql_check_user = "SELECT id FROM users WHERE id = ?";
$stmt_check_user = $conn->prepare($sql_check_user);
$stmt_check_user->bind_param("i", $userId);
$stmt_check_user->execute();
$stmt_check_user->store_result();

if ($stmt_check_user->num_rows == 0) {
    echo "Erro: O usuário não existe.";
    exit();
}
$stmt_check_user->close();

$sql_metas = "SELECT id_meta, nome_meta, valor_alvo, valor_atual, prazo FROM metas_usuario WHERE id_usuario = ?";
$stmt_metas = $conn->prepare($sql_metas);
$stmt_metas->bind_param("i", $userId);
$stmt_metas->execute();
$result = $stmt_metas->get_result();

include('../../config/conteudos/metas/adicionar_meta.php');
include('../../config/conteudos/metas/logica_metas.php');
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Minhas Metas - Neo Finance</title>
    <link rel="stylesheet" href="../../css/conteudos/metas/metas.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.4.0/dist/confetti.browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="titulo-metas">
            <h1>Metas<img src="../../assets/icons/home--sidebar/metas--icon.svg" alt=""></h1>
        </div>

        <div class="menu-criar-meta" id="menuCriarMeta">
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
                <div class="adicionar--btn" id="adicionarBtn">
                    <img src="../../assets/icons/add--icon.svg" alt="add--btn">
                </div>
            </div>

            <div class="card-container">
                <?php while ($row = $result->fetch_assoc()):
                    $goalId = $row['id_meta'];
                    $deadline = date('d/m/Y', strtotime($row['prazo']));
                    ?>
                    <div class="card-meta" data-id="<?php echo $goalId; ?>">
                        <div class="titulo-card">
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
                                <div class="barra-progresso-preenchida" style="width: <?php echo ($row['valor_atual'] / $row['valor_alvo']) * 100; ?>%;"></div>
                            </div>
                            <span>R$ <?php echo $row['valor_atual']; ?> de R$ <?php echo $row['valor_alvo']; ?></span>
                            <?php if ($row['valor_atual'] >= $row['valor_alvo']): ?>
                                <div class="mensagem-meta-alcancada">Meta Alcançada!</div>
                                <?php if (!isset($_SESSION['celebrated_goals']) || !in_array($goalId, $_SESSION['celebrated_goals'])):
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
                            <h2>Até <?php echo $deadline; ?></h2>
                        </div>

                        <div class="grafico">
                        <div id="chart-<?php echo $goalId; ?>" class="centered-chart" style="height: 200px; width: 200px;"></div>              
                        </div>
                        

                        <button class="selectActionBtn">Selecionar Ação</button>

                        <div class="actionButtons" style="display:none;">
                            <button class="actionBtn" data-action="depositar">Depositar<img src="../../assets/icons/icon--depositar--meta.svg" alt=""></button>
                            <button class="actionBtn" data-action="resgatar">Resgatar<img src="../../assets/icons/icon--resgatar--metas.svg" alt=""></button>
                            <button class="actionBtn" data-action="verHistorico">Ver Histórico<img src="../../assets/icons/icon--historico--metas.svg" alt=""></button>
                        </div>

                        <div class="container-formularios">
                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-resgatar" style="display:none;">
                                <button type="button" class="back-btn">&lt;</button>
                                <input type="hidden" name="goal_id" value="<?php echo $goalId; ?>">
                                <label for="withdraw_value">Valor a Resgatar:</label>
                                <input type="number" id="withdraw_value" name="withdraw_value" required>
                                <button type="submit" name="resgatar" class="btn--resgatar">Resgatar</button>
                            </form>

                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="form-depositar" style="display:none;">
                                <button type="button" class="back-btn">&lt;</button>
                                <input type="hidden" name="goal_id" value="<?php echo $goalId; ?>">
                                <label for="deposit_value">Valor a Depositar:</label>
                                <input type="number" id="deposit_value" name="deposit_value" required>
                                <button type="submit" name="depositar" class="btn--depositar">Depositar</button>
                            </form>

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
                                    $sql_history = "SELECT tipo_transacao, valor, data_transacao FROM historico_transacoes WHERE id_usuario = ? AND id_meta = ?";
                                    $stmt_history = $conn->prepare($sql_history);
                                    $stmt_history->bind_param("ii", $userId, $goalId);
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

            showCard(currentIndex);

            <?php foreach ($result as $meta) { ?>
                var valorDepositado = <?php echo $meta['valor_atual']; ?>;
                var valorAlvo = <?php echo $meta['valor_alvo']; ?>;
                var progresso = Math.min(Math.round((valorDepositado / valorAlvo) * 100), 100); // Limita a porcentagem a 100%

                var chartDom<?php echo $meta['id_meta']; ?> = document.getElementById('chart-<?php echo $meta['id_meta']; ?>');
                var myChart<?php echo $meta['id_meta']; ?> = echarts.init(chartDom<?php echo $meta['id_meta']; ?>);
                var option<?php echo $meta['id_meta']; ?> = {
                    tooltip: {
                        show: false // Desabilita o tooltip
                    },
                    series: [
                        {
                            name: 'Progresso',
                            type: 'pie',
                            radius: ['50%', '70%'],
                            avoidLabelOverlap: false,
                            label: {
                                show: true,
                                position: 'center',
                                formatter: function() {
                                    return progresso + '%'; // Mostra a porcentagem do valor depositado
                                },
                                fontSize: '20',
                                fontWeight: 'bold'
                            },
                            labelLine: {
                                show: false
                            },
                            data: [
                                { value: progresso, name: 'Progresso', itemStyle: { color: '#28a745' } },
                                { value: 100 - progresso, name: 'Restante', itemStyle: { color: '#f0f0f0' } }
                            ]
                        }
                    ]
                };
                myChart<?php echo $meta['id_meta']; ?>.setOption(option<?php echo $meta['id_meta']; ?>);
            <?php } ?>
        });
    </script>

</body>

</html>
<?php $conn->close(); ?>
