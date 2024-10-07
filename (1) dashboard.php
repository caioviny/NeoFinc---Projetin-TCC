<?php
session_start();
include("../../config/database/conexao.php");
include("../../config/conteudos/calendario/funcoes.php");

// Verifica se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: ../../views/login/login.php"); // Redireciona para a página de login
  exit();
}

$userId = $_SESSION['user_id']; // ID do usuário logado

// Consultar receitas e despesas
$queryReceitas = "SELECT SUM(valor) AS totalReceitas FROM transacoes WHERE tipo = 'receita' AND usuario_id = $userId";
$queryDespesas = "SELECT SUM(valor) AS totalDespesas FROM transacoes WHERE tipo = 'despesa' AND usuario_id = $userId";

$resultReceitas = mysqli_query($conn, $queryReceitas);
$resultDespesas = mysqli_query($conn, $queryDespesas);

// Extrair valores
$receitas = mysqli_fetch_assoc($resultReceitas)['totalReceitas'] ?? 0;
$despesas = mysqli_fetch_assoc($resultDespesas)['totalDespesas'] ?? 0;
$total = $receitas + $despesas;
$proporcaoReceitas = ($total > 0) ? ($receitas / $total) * 800 : 0; // Largura em pixels
$proporcaoDespesas = ($total > 0) ? ($despesas / $total) * 800 : 0; // Largura em pixels


// Calcular Balanço Total
$balanco = $receitas - $despesas;

// Consultar os 5 últimos lançamentos
$queryHistorico = "SELECT tipo, nome, valor, data FROM transacoes WHERE usuario_id = $userId ORDER BY data DESC LIMIT 5";
$resultHistorico = mysqli_query($conn, $queryHistorico);

// Consultar o próximo vencimento a partir de hoje
$queryVencimentos = "SELECT descricao, data_vencimento, valor, categoria 
                     FROM vencimentos 
                     WHERE usuario_id = $userId 
                     AND status = 'Pendente' 
                     AND data_vencimento >= CURDATE() 
                     ORDER BY data_vencimento ASC 
                     LIMIT 1";
$resultVencimentos = mysqli_query($conn, $queryVencimentos);

// Extrair o próximo vencimento
$vencimento = mysqli_fetch_assoc($resultVencimentos);

// Verifica se existe um vencimento
if ($vencimento) {
  // Se existir, armazene as informações
  $descricao = $vencimento['descricao'];
  $data_vencimento = $vencimento['data_vencimento'];
  $valor = $vencimento['valor'];
  $categoria = $vencimento['categoria'];
} else {
  // Caso não exista, defina valores padrão
  $descricao = "Sem vencimentos pendentes";
  $data_vencimento = "";
  $valor = 0;
  $categoria = "";
}

// Função para traduzir o mês para português
function mesEmPortugues($data)
{
  $meses = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro'
  ];
  return $meses[(int) date('m', strtotime($data))];
}

/* ======================
  SELECT CATEGORIAS
=========================*/

// Consultando para selecionar todas as categorias
$sql = "SELECT id, nome FROM categorias";
$result = $conn->query($sql);

// Criando uma variável para armazenar as opções
$options = "";

// Verifica se encontrou resultados
if ($result->num_rows > 0) {
  // Itera pelos resultados e gera as opções
  while ($row = $result->fetch_assoc()) {
    $options .= '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nome']) . '</option>';
  }
} else {
  // Caso não existam categorias
  $options .= '<option value="">Nenhuma categoria encontrada</option>';
}

/* =================================
Verifica se o formulário foi enviado
====================================*/

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nome = mysqli_real_escape_string($conn, $_POST['nome']);
  $valor = mysqli_real_escape_string($conn, $_POST['valor']);
  $categoria = mysqli_real_escape_string($conn, $_POST['categoria']);
  $tipo = mysqli_real_escape_string($conn, $_POST['tipo']); // Adicionei este campo

  // Insere os dados na tabela transacoes
  $sql = "INSERT INTO transacoes (nome, valor, categoria_id, tipo, usuario_id) 
          VALUES ('$nome', '$valor', '$categoria', '$tipo', $userId)";

  if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Salvo com sucesso');</script>";
  } else {
    echo "<script>alert('Erro ao salvar: " . mysqli_error($conn) . "');</script>";
  }
}


/* ============
 Consultar histórico recente de transações
===============*/

$queryHistorico = "SELECT nome, valor, tipo, data 
                   FROM transacoes 
                   WHERE usuario_id = $userId 
                   ORDER BY data DESC 
                   LIMIT 5";
$resultHistorico = mysqli_query($conn, $queryHistorico);

// Inicializar uma variável para armazenar os itens do histórico
$historicoItems = "";

// Verifica se encontrou resultados
if (mysqli_num_rows($resultHistorico) > 0) {
    while ($row = mysqli_fetch_assoc($resultHistorico)) {
        $tipoIcon = $row['tipo'] === 'receita' ? 'icon-receita.svg' : 'icon-despesa.svg'; // Defina os ícones conforme seu design
        $historicoItems .= '<li>
            <span class="tipo"><img src="../../assets/icons/' . $tipoIcon . '" alt="' . $row['tipo'] . ' icon"></span>
            <span class="nome">' . htmlspecialchars($row['nome']) . '</span>
            <span class="valor">R$ ' . number_format($row['valor'], 2, ',', '.') . '</span>
            <span class="data">' . date('d/m/Y H:i', strtotime($row['data'])) . '</span>
        </li>';
    }
} else {
    $historicoItems .= '<li>Nenhuma transação recente encontrada.</li>';
}

// Fecha a conexão
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Neo Finance - Dashboard</title>
  <link rel="stylesheet" href="../../css/conteudos/dashboard/dashboard.css">
  <link rel="stylesheet" href="../../css/conteudos/dashboard/popUp.css">
</head>

<body>
  <!-- Início Header -->
  <div class="container--header">
    <header class="perfil">
      <div class="usuario">
        <span><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></span>

        <h1>Hello, <?php echo $_SESSION['username']; ?>!</h1>
      </div>
      <div class="notificacao--usuario">
        <img src="../../assets/icons/sino--icon.svg" alt="icon-notificacao" />
      </div>
    </header>
  </div>
  <!-- Fim Header -->

  <!-- Início Conteúdo -->
  <div class="container--dashboard">
    <div class="cards">
      <!-- Card Balanço Total -->
      <div class="card--balanco">
        <!-- Lado Esquerdo do Card -->
        <div class="lado--esquerdo-bt">
          <span>Balanço Total</span>
          <h1 id="balanco--valor--total">R$ <?php echo number_format($balanco, 2, ',', '.'); ?></h1>
        </div>
        <!-- Fim Lado Esquerdo do Card -->

        <!-- Lado Direito do Card -->
        <div class="lado--direito-geral-bt">
          <div class="lado--direito-bt">
            <div class="parte--cima-bt">
              <div class="info--valores">
                <span>Saldo</span>
                <span id="resultado--receita">R$ <?php echo number_format($receitas, 2, ',', '.'); ?></span>
              </div>
              <img src="../../assets/icons/icon--saldo.svg" alt="icon--saldo" />
            </div>
            <div class="parte--baixo-bt">
              <div class="info--valores">
                <span>Gastos</span>
                <span id="resultado--despesa">R$ <?php echo number_format($despesas, 2, ',', '.'); ?></span>
              </div>
              <img src="../../assets/icons/icon--gastos.svg" alt="icon--gastos" />
            </div>
          </div>
          <!-- Botão Adicionar -->
          <div class="botao--adicionar">

            <img id="btn--abrir--popup" src="../../assets/icons/botao--adicionar.svg" alt="Adicionar" />

          </div>
        </div>
      </div>
      <!-- Fim Card Balanço Total -->

      <!-- Card Histórico Recente -->
      <div class="card--historico-recente">
        <div class="header--card-hr">
          <span>Histórico Recente</span>
          <button>Ver tudo</button>
        </div>
        <!-- Histórico de Transações -->
        <div class="info--historico">
          <ul id="historicoList">
            <?php echo $historicoItems; // Itens do histórico serão exibidos aqui ?>
          </ul>
        </div>
        <div class="seta--pra--baixo"></div>
      </div>
      <!-- Fim Histórico Recente -->




      <!-- Card Receitas x Despesas -->
      <div class="card--receitasXdespesas">
        <!-- Lado Esquerdo do Card -->
        <div class="lado--esquerdo-rd">
          <span>Receitas x Despesas</span>
          <div class="grafico--receitasXdespesas">
            <div class="grafico--receitas" style="width: <?php echo $proporcaoReceitas; ?>px;"></div>
            <div class="grafico--despesas" style="width: <?php echo $proporcaoDespesas; ?>px;"></div>
          </div>
        </div>
        <!-- Informações e Filtro -->
        <div class="infoXfiltro">
          <div class="select--filtro">
            <select name="Meses" id="Filtro--mes">
              <option value="mensal">Mensal</option>
              <option value="semanal">Semanal</option>
              <option value="diario">Diário</option>
            </select>
          </div>
          <div class="receitas--filtro">
            <div class="icon--verde"></div>
            <div class="info--valores">
              <span>Receitas</span>
              <span>R$ <?php echo number_format($receitas, 2, ',', '.'); ?></span>
            </div>
          </div>
          <div class="despesas--filtro">
            <div class="icon--vermelho"></div>
            <div class="info--valores">
              <span>Despesas</span>
              <span>R$ <?php echo number_format($despesas, 2, ',', '.'); ?></span>
            </div>
          </div>
          <div class="saldo--filtro">
            <div class="icon--verde-claro"></div>
            <div class="info--valores">
              <span>Saldo</span>
              <span>R$ <?php echo number_format($balanco, 2, ',', '.'); ?></span>
            </div>
          </div>
        </div>
      </div>


      <div class="card--vencimentos">
        <div class="header--card-v">
          <div class="titulo--header-v">
            <img src="../../assets/icons/icon--calendario.svg" alt="icon--calendario" />
            <span class="dias--restantes"><?php echo calcularDiasRestantes($data_vencimento); ?></span>
          </div>
          <span class="mes--vencimento"><?php echo mesEmPortugues($data_vencimento); ?></span>
        </div>
        <div class="info--vencimentos">
          <div class="info--descricao">
            <span class="data--vencimento"><?php echo date('d', strtotime($data_vencimento)); ?></span>
            <div class="descricao--vencimento">
              <span><?php echo $descricao; ?></span>
              <span><?php echo $categoria; ?></span>
            </div>
          </div>
          <div class="linha--vertical-v"></div>
          <span class="valor--vencimento">R$ <?php echo number_format($valor, 2, ',', '.'); ?></span>
        </div>
      </div>
      <!-- Fim Card Próximos Vencimentos -->



      <!-- Card Lembretes -->
      <div class="card--lembretes">
        <div class="header--card-l">
          <span class="titulo">Lembretes</span>
          <span class="descricao--lembrete">Moradia</span>
        </div>
        <div class="info--lembrete">
          <div class="detalhes--info">
            <span class="descricao--info">Pagar aluguel</span>
            <span class="valor--lembrete">$ 350,00</span>
          </div>
          <div class="status--info">
            <span>Em aberto</span>
            <input type="checkbox" name="status--checkbox" />
          </div>
        </div>
      </div>
      <!-- Fim Card Lembretes -->
    </div>
  </div>
  <!-- Fim Conteúdo -->


  <form method="POST" action="">
    <label for="nome">Nome:</label>
    <input type="text" name="nome" required>

    <label for="valor">Valor:</label>
    <input type="number" name="valor" required>

    <label for="categoria">Categoria:</label>
    <select name="categoria" required>
      <?php echo $options; // Supondo que $options tenha as categorias carregadas anteriormente ?>
    </select>

    <label for="tipo">Tipo:</label>
    <select name="tipo" required>
      <option value="receita">Receita</option>
      <option value="despesa">Despesa</option>
    </select>

    <button type="submit">Adicionar</button>
  </form>
</body>

</html>