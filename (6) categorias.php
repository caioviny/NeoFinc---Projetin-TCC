<?php
// Iniciar a sessão
session_start();

// Incluir a conexão com o banco de dados
include('../../config/database/conexao.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
  header("Location: ../login/login.php");
  exit();
}

$usuario_id = $_SESSION['user_id']; // Pega o ID do usuário logado da sessão

// Buscar todas as categorias do usuário logado
$query = "SELECT * FROM categorias WHERE usuario_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Neo Finance - Categorias</title>
  <link rel="stylesheet" href="../../css/conteudos/categorias/categorias.css">
  <style>
    /* Estilo geral do modal */
    .modal {
      display: none;
      /* Inicialmente oculto */
      position: fixed;
      /* Fica fixo na tela */
      z-index: 1000;
      /* Fica acima de outros elementos */
      left: 0;
      top: 0;
      width: 100%;
      /* Largura total da tela */
      height: 100%;
      /* Altura total da tela */
      overflow: auto;
      /* Habilita rolagem se necessário */
      background-color: rgba(0, 0, 0, 0.5);
      /* Fundo semitransparente */
    }

    /* Estilo do conteúdo do modal */
    .modal-conteudo {
      background-color: #ffffff;
      /* Fundo branco para o modal */
      margin: 10% auto;
      /* Centraliza o modal verticalmente */
      padding: 30px;
      /* Ajustado para mais espaçamento */
      border: 1px solid #ccc;
      /* Borda cinza clara */
      border-radius: 12px;
      /* Bordas mais arredondadas */
      width: 80%;
      /* Largura do modal */
      max-width: 600px;
      /* Largura máxima do modal */
      display: flex;
      /* Usar flexbox para organização */
      flex-direction: column;
      /* Organiza os itens em coluna */
      align-items: center;
      /* Centraliza o conteúdo horizontalmente */
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      /* Sombra leve para destaque */
    }

    /* Estilo do título do modal */
    .modal-conteudo h2 {
      margin-bottom: 20px;
      /* Espaçamento inferior */
      text-align: center;
      /* Centraliza o texto */
      font-size: 24px;
      /* Tamanho da fonte maior para o título */
      color: #333;
      /* Cor do texto do título */
    }

    /* Estilo dos botões de fechar e confirmar */
    .fechar,
    .botoes-excluir button {
      display: flex;
      cursor: pointer;
      /* Muda o cursor para indicar que é clicável */
      background-color: #4CAF50;
      /* Cor do botão */
      color: white;
      /* Texto branco */
      padding: 10px 20px;
      /* Espaçamento interno do botão */
      border: none;
      /* Sem borda */
      border-radius: 5px;
      /* Bordas arredondadas */
      margin: 5px;
      /* Espaçamento entre os botões */
      transition: background-color 0.3s;
      /* Transição suave na cor */
    }

    /* Estilo do botão ao passar o mouse */
    .botoes-excluir button:hover,
    .fechar:hover {
      background-color: #45a049;
      /* Cor ao passar o mouse */
    }

    /* Estilo dos inputs */
    input[type="text"],
    input[type="radio"] {
      width: 100%;
      /* Largura total do input */
      padding: 10px;
      /* Espaçamento interno */
      margin-bottom: 15px;
      /* Maior espaçamento inferior */
      border: 1px solid #ccc;
      /* Borda cinza clara */
      border-radius: 5px;
      /* Bordas arredondadas */
    }

    /* Estilo do container de ícones */
    .container--icones {
      display: flex;
      /* Usar flexbox para organizar os ícones */
      flex-wrap: wrap;
      /* Permitir que os ícones quebrem para a próxima linha */
      justify-content: center;
      /* Centraliza os ícones horizontalmente */
      margin-bottom: 20px;
      /* Espaçamento inferior */
    }

    /* Estilo de cada ícone */
    .icon {
      display: grid;
      /* Usar flexbox para centralizar o ícone */
      flex-direction: column;
      /* Organiza ícone e label em coluna */
      align-items: center;
      /* Centraliza horizontalmente */
      margin: 10px;
      /* Espaçamento entre os ícones */
    }

    .icon label {
      cursor: pointer;
      /* Indica que é clicável */
    }

    .mensagem {
      padding: 10px;
      margin: 20px 0;
      border-radius: 5px;
      text-align: center;
    }

    .sucesso {
      background-color: #dff0d8;
      color: #3c763d;
    }

    .erro {
      background-color: #f2dede;
      color: #a94442;
    }
  </style>
</head>

<body>
  <!-- Início do Header -->
  <div class="container--header">
    <header class="banner">
      <div class="titulo--banner">
        <img src="../../assets/icons/categoria--icon.svg" alt="categoria--icon" />
        <h1>Categorias</h1>
      </div>
    </header>
  </div>
  <!-- Fim do Header -->

  <!-- Início do Conteúdo Principal -->
  <div class="container--conteudo">
    <!-- Botão de Adicionar -->
    <div class="adicionar--btn">
      <img src="../../assets/icons/add--icon.svg" alt="add--btn" onclick="abrirModalAdicionar()">
    </div>

   <!-- Início dos Cards de Categorias -->
<div class="cards--categorias">
  <?php while ($row = $result->fetch_assoc()): ?>
    <!-- Card Individual de Categoria -->
    <div class="card--categoria">
      <!-- Lado Esquerdo do Card -->
      <div class="lado--esquerdo-card">
        <div class="icon--categoria">
          <!-- Exibir o ícone da categoria -->
          <i class="<?php echo htmlspecialchars($row['icone']); ?>"></i>
        </div>
        <div class="descricao--categoria">
          <span><?php echo htmlspecialchars($row['nome']); ?></span>
        </div>
      </div>
      <!-- Fim do Lado Esquerdo do Card -->

      <!-- Lado Direito do Card -->
      <div class="lado--direito-card">
        <div class="icon--apagar">
          <!-- Botão de apagar categoria -->
          <img src="../../assets/icons/lixeira--icon.svg" alt="icon--excluir" onclick="abrirModalExcluir('<?php echo htmlspecialchars($row['nome']); ?>', '<?php echo $row['id']; ?>')">
        </div>
        <div class="icon--editar">
          <!-- Botão de editar categoria -->
          <img src="../../assets/icons/lapis--icon.svg" alt="icon--editar" onclick='abrirModalEditar(<?php echo json_encode($row); ?>)'>
        </div>
      </div>
      <!-- Fim do Lado Direito do Card -->
    </div>
    <!-- Fim do Card Individual de Categoria -->
  <?php endwhile; ?>
</div>
<!-- Fim dos Cards de Categorias -->

  </div>
  <!-- Fim do Conteúdo Principal -->

  <!-- Modal de Adicionar Categoria -->
  <div id="modalAdicionar" class="modal">
    <div class="modal-conteudo">
      <span class="fechar" onclick="fecharModal('modalAdicionar')">&times;</span>
      <h2>Adicionar Categoria</h2>
      <form id="formAdicionar" method="POST" action="../../config/conteudos/categorias/adicionar_categoria.php">
        <label for="nomeAdicionar">Nome da Categoria:</label>
        <input type="text" id="nomeAdicionar" name="nome" required>

        <label for="iconeAdicionar">Ícone da Categoria:</label>
        <button type="button" id="botaoSelecionarIcone" onclick="toggleListaIcones()">Selecionar Ícone</button>
        <div id="listaIcones" class="container--icones" style="display: none;">
          <!-- Seus ícones vão aqui -->
          <div class="container--icones">
            <div class="icon">
              <input type="radio" name="icone" value="fi-br-scissors" id="icon1" required>
              <label for="icon1"><i class="fi fi-br-scissors"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-home" id="icon2" required>
              <label for="icon2"><i class="fi fi-sr-home"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-br-smartphone" id="icon3" required>
              <label for="icon3"><i class="fi fi-br-smartphone"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-file-invoice-dollar" id="icon4" required>
              <label for="icon4"><i class="fi fi-sr-file-invoice-dollar"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-br-money-coin-transfer" id="icon5" required>
              <label for="icon5"><i class="fi fi-br-money-coin-transfer"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-ss-plane-alt" id="icon6" required>
              <label for="icon6"><i class="fi fi-ss-plane-alt"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-ss-bus-alt" id="icon7" required>
              <label for="icon7"><i class="fi fi-ss-bus-alt"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-ss-wrench-alt" id="icon8" required>
              <label for="icon8"><i class="fi fi-ss-wrench-alt"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-ss-car-mechanic" id="icon9" required>
              <label for="icon9"><i class="fi fi-ss-car-mechanic"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-shopping-cart" id="icon10" required>
              <label for="icon10"><i class="fi fi-sr-shopping-cart"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-wallet" id="icon11" required>
              <label for="icon11"><i class="fi fi-sr-wallet"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-gamepad" id="icon12" required>
              <label for="icon12"><i class="fi fi-sr-gamepad"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-ss-hotdog" id="icon13" required>
              <label for="icon13"><i class="fi fi-ss-hotdog"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-user-md" id="icon14" required>
              <label for="icon14"><i class="fi fi-sr-user-md"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-dog-leashed" id="icon15" required>
              <label for="icon15"><i class="fi fi-sr-dog-leashed"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-bone" id="icon16" required>
              <label for="icon16"><i class="fi fi-sr-bone"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-cat" id="icon17" required>
              <label for="icon17"><i class="fi fi-sr-cat"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-devices" id="icon18" required>
              <label for="icon18"><i class="fi fi-sr-devices"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-ss-book-alt" id="icon19" required>
              <label for="icon19"><i class="fi fi-ss-book-alt"></i></label>
            </div>
          </div>
          <!-- Adicione mais ícones conforme necessário -->
        </div>

        <button type="submit">Adicionar</button>
      </form>
    </div>
  </div>

  <div id="modalEditar" class="modal">
    <div class="modal-conteudo">
      <span class="fechar" onclick="fecharModal('modalEditar')">&times;</span>
      <h2>Editar Categoria</h2>
      <form id="formEditar" method="POST" action="../../config/conteudos/categorias/editar_categoria.php">
        <input type="hidden" name="id_categoria" id="id_categoria" required>
        <label for="nomeEditar">Nome da Categoria:</label>
        <input type="text" id="nomeEditar" name="nome" required>
        <label for="iconeEditar">Ícone da Categoria (opcional):</label>
        <button type="button" id="botaoSelecionarIconeEditar" onclick="toggleListaIconesEditar()">Selecionar Ícone</button>
        <input type="hidden" name="icone" id="iconeSelecionadoEditar">
        <div id="listaIconesEditar" class="container--icones" style="display: none;">
          <!-- Ícones -->
          <div class="container--icones">
            <div class="icon">
              <input type="radio" name="icone" value="fi-br-scissors" id="icon1">
              <label for="icon1"><i class="fi fi-br-scissors"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-home" id="icon2">
              <label for="icon2"><i class="fi fi-sr-home"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-br-smartphone" id="icon3">
              <label for="icon3"><i class="fi fi-br-smartphone"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-file-invoice-dollar" id="icon4">
              <label for="icon4"><i class="fi fi-sr-file-invoice-dollar"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-br-money-coin-transfer" id="icon5">
              <label for="icon5"><i class="fi fi-br-money-coin-transfer"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-ss-plane-alt" id="icon6">
              <label for="icon6"><i class="fi fi-ss-plane-alt"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-ss-bus-alt" id="icon7">
              <label for="icon7"><i class="fi fi-ss-bus-alt"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-ss-wrench-alt" id="icon8">
              <label for="icon8"><i class="fi fi-ss-wrench-alt"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-ss-car-mechanic" id="icon9">
              <label for="icon9"><i class="fi fi-ss-car-mechanic"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-shopping-cart" id="icon10">
              <label for="icon10"><i class="fi fi-sr-shopping-cart"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-wallet" id="icon11">
              <label for="icon11"><i class="fi fi-sr-wallet"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-gamepad" id="icon12">
              <label for="icon12"><i class="fi fi-sr-gamepad"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-ss-hotdog" id="icon13">
              <label for="icon13"><i class="fi fi-ss-hotdog"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-user-md" id="icon14">
              <label for="icon14"><i class="fi fi-sr-user-md"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-dog-leashed" id="icon15">
              <label for="icon15"><i class="fi fi-sr-dog-leashed"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-bone" id="icon16">
              <label for="icon16"><i class="fi fi-sr-bone"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-cat" id="icon17">
              <label for="icon17"><i class="fi fi-sr-cat"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-sr-devices" id="icon18">
              <label for="icon18"><i class="fi fi-sr-devices"></i></label>
            </div>
            <div class="icon">
              <input type="radio" name="icone" value="fi-ss-book-alt" id="icon19">
              <label for="icon19"><i class="fi fi-ss-book-alt"></i></label>
            </div>
          </div>
          <!-- Adicione mais ícones conforme necessário -->
        </div>
        <button type="submit">Salvar</button>
      </form>
    </div>
  </div>


  <!-- Modal de Exclusão -->
  <div id="modalExcluir" class="modal">
    <div class="modal-conteudo">
      <span class="fechar" onclick="fecharModal('modalExcluir')">&times;</span>
      <h2>Excluir Categoria</h2>
      <p>Você tem certeza de que deseja excluir esta categoria?</p>
      <p><strong id="nomeCategoriaExcluir">Nome da Categoria</strong></p> <!-- Nome da categoria a ser excluída -->
      <form id="formExcluir" method="POST" action="../../config/conteudos/categorias/excluir_categoria.php">
        <input type="hidden" name="id_categoria" id="id_categoria_excluir" required>
        <div class="botoes-excluir">
          <button type="button" class="fechar" onclick="fecharModal('modalExcluir')">Cancelar</button>
          <button type="submit">Excluir</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function abrirModalAdicionar() {
      document.getElementById('modalAdicionar').style.display = 'block';
    }

    function abrirModalEditar(categoria) {

      document.getElementById('id_categoria').value = categoria.id;
      document.getElementById('nomeEditar').value = categoria.nome;
      document.querySelector(`input[name="icone"][value="${categoria.icone}"]`).checked = true;
      document.getElementById('modalEditar').style.display = 'block';
      console.log(categoria); // Verifique os dados da categoria
    }

    function fecharModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    function toggleListaIcones() {
      var listaIcones = document.getElementById('listaIcones');
      listaIcones.style.display = (listaIcones.style.display === 'none' || listaIcones.style.display === '') ? 'block' : 'none';
    }

    function toggleListaIconesEditar() {
      var listaIcones = document.getElementById('listaIconesEditar');
      listaIcones.style.display = (listaIcones.style.display === 'none' || listaIcones.style.display === '') ? 'block' : 'none';
    }

    function abrirModalExcluir(nome, id) {
      document.getElementById('nomeCategoriaExcluir').innerText = nome;
      document.getElementById('id_categoria_excluir').value = id;
      document.getElementById('modalExcluir').style.display = 'block';
    }

    function selecionarIcone(icone) {
      document.getElementById('iconeSelecionadoEditar').value = icone; // Define o valor do campo oculto
    }

    function confirmarExclusao() {
      var categoriaId = document.getElementById('botaoConfirmarExcluir').getAttribute('data-id'); // Recupera o ID da categoria
      // Redireciona para a página de exclusão com o ID da categoria
      window.location.href = '../../config/conteudos/categorias/excluir_categoria.php?id=' + categoriaId;
    }
  </script>

</body>

</html>