<?php
include '../../config/database/conexao.php';

// Query para selecionar as categorias
$sql = "SELECT id, nome, icone FROM categorias";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Neo Finance - Categorias</title>
  <link rel="stylesheet" href="../../css/conteudos/categorias/categorias.css" />
  <link rel="stylesheet" href="../../css/conteudos/categorias/popUpCategoria.css">
</head>

<body>
  <!-- Início do Header -->
  <div class="container--header">
    <header class="banner">
      <div class="titulo--banner">
        <h1>Categorias</h1>
        <img src="../../assets/icons/categoria--icon.svg" alt="categoria--icon" />
      </div>
    </header>
  </div>
  <!-- Fim do Header -->

  <!-- Início do Conteúdo Principal -->
  <div class="container--conteudo">
    <!-- Botão de Adicionar -->
    <div class="adicionar--btn" onclick="abrirPopup()">
      <img src="../../assets/icons/add--icon.svg" alt="adicionar--btn" />
    </div>

    <!-- Início dos Cards de Categorias -->
    <div class="cards--categorias" id="cards-categorias">
      <?php
      if ($result->num_rows > 0) {
        // Exibe as categorias
        while ($row = $result->fetch_assoc()) {
          echo '<div class="card--categoria">';
          // Lado Esquerdo do Card
          echo '  <div class="lado--esquerdo-card">';
          echo '    <div class="icon--categoria"><i class="' . $row["icone"] . '"></i></div>';
          echo '    <div class="descricao--categoria">';
          echo '      <span>' . $row["nome"] . '</span>';
          echo '    </div>';
          echo '  </div>';
          // Fim do Lado Esquerdo do Card
      
          // Lado Direito do Card
          echo '  <div class="lado--direito-card">';
          echo '    <div class="icon--apagar" data-id="' . $row["id"] . '">';
          echo '      <img src="../../assets/icons/lixeira--icon.svg" alt="icon--lixeira" />';
          echo '    </div>';
          echo '    <div class="icon--editar">';
          echo '      <img src="../../assets/icons/lapis--icon.svg" alt="icon--editar" />';
          echo '    </div>';
          echo '  </div>';

          // Fim do Lado Direito do Card
          echo '</div>';
        }
      } else {
        echo "<p>Nenhuma categoria encontrada.</p>";
      }
      $conn->close();
      ?>
    </div>
    <!-- Fim dos Cards de Categorias -->



    <!-- Fim do Conteúdo Principal -->


    <!-- INICIO | ÁREA DOS POPUPS -->
    <!-- INICIO | POPUP ADICIONAR CATEGORIA NOVA -->
    <div class="popup--categoria" id="id--popup--categoria">
      <div class="popup--categoria--conteudo">
        <span class="fecha--btn--categoria" id="id--fecha--btn--categoria">&times;</span>
        <h2>Adicionar nova categoria</h2>
        <div class="box--categoria--nome">
          <div class="box--categoria--nome--item">
            <label for="nome--categoria">Nome</label>
            <div class="input--categoria">
              <input type="text" placeholder="Digite a nova categoria" id="id--nova--categoria" required>
            </div>
          </div>
        </div>
        <div class="box--icone--categoria">
          <button class="selecione-icone" id="selecione-icone">Selecione um ícone</button>
          <div class="icone--grade" id="icone-grade" style="display: none;">
            <div class="icon" data-icon="fi-sr-home"><i class="fi fi-sr-home"></i></div>
            <div class="icon" data-icon="fi-br-smartphone"><i class="fi fi-br-smartphone"></i></div>
            <div class="icon" data-icon="fi-sr-file-invoice-dollar"><i class="fi fi-sr-file-invoice-dollar"></i></div>
            <div class="icon" data-icon="fi-br-money-coin-transfer"><i class="fi fi-br-money-coin-transfer"></i></div>
            <div class="icon" data-icon="fi-ss-plane-alt"><i class="fi fi-ss-plane-alt"></i></div>
            <!-- Adicione mais ícones conforme necessário -->
          </div>
        </div>
        <input type="hidden" id="icone-selecionado" value=""> <!-- Campo oculto para armazenar o ícone selecionado -->
        <button class="enviar--nova--categoria" id="enviar-nova-categoria">Enviar</button>
      </div>
    </div>
    <!-- FIM | POPUP ADICIONAR CATEGORIA NOVA -->

    <!-- INICIO | POPUP EXCLUIR -->
    <div class="popup--excluir" id="id--pop--up--excluir">
      <div class="popup--excluir--conteudo">
        <span class="fecha--btn--excluir" id="id--fecha--popup--excluir">&times;</span>
        <h1>Deseja realmente excluir essa categoria?</h1>
        <div class="checkbox--nao--perguntar">
          <input type="checkbox" id="nao--perguntar--novamente" name="nao--perguntar--novamente">
          <label for="nao--perguntar--novamente">Não perguntar novamente</label>
        </div>
        <div class="acoes--popup--excluir">
          <button class="btn--confirmar" id="confirmar--exclusao">SIM</button>
          <button class="btn--cancelar" id="cancelar--exclusao">NÃO</button>
        </div>
      </div>
    </div>
    <!-- FIM | POPUP EXCLUIR -->

    <!-- INICIO | POPUP EDITAR -->
    <div class="popup--editar" id="id--popup--editar">
      <div class="popup--editar--conteudo">
        <span class="fecha--btn--editar" id="id--fecha--popup--editar">&times;</span>
        <h1>Editar Categorias</h1>
        <div class="box--select--categorias">
          <div class="select--categorias">
            <button id="abrir--selecao--categorias" type="button" class="btn--selecao">Selecione Categoria</button>
            <div id="selecao--categoria--container" data-icon="" class="categoria--icone"></div>
          </div>
        </div>
        <div class="area--edicao">
          <label for="edicao--nova--categoria">Editar Nome:</label>
          <input type="text" id="edicao--nova--categoria" class="novoNome--categoria"
            placeholder="Digite o novo nome...">
          <div class="edicao--icone">
            <label for="edicao--novo--icone">Editar Ícone:</label>
            <button id="abrir--selecao-icones" type="button" class="btn--selecao">Selecione o Ícone</button>
          </div>
        </div>
        <div class="popup--editar--botoes">
          <button class="btn--confirmar--edicao">Salvar</button>
          <button class="btn--cancelar--edicao" id="id--fecha--popup--editar">Cancelar</button>
        </div>
      </div>
    </div>
    <!-- FIM | POPUP EDITAR -->
    <!-- FIM | ÁREA DOS POPUPS -->

    <script src="../../js/conteudos/categorias/popups_categoria.js"></script>
    <script>


      /* =================
        ENVIO DE CATEGORIA
      =====================*/
      document.addEventListener('DOMContentLoaded', () => {
        // Abre o popup
        const botaoAdicionar = document.querySelector('.adicionar--btn');
        botaoAdicionar.addEventListener('click', abrirPopup);

        // Seleciona o ícone
        const icones = document.querySelectorAll('.icon');
        icones.forEach(icon => {
          icon.addEventListener('click', function () {
            document.getElementById('icone-selecionado').value = this.getAttribute('data-icon');
            document.getElementById('icone-grade').style.display = 'none'; // Oculta a grade de ícones
          });
        });

        // Envia a nova categoria
        document.getElementById('enviar-nova-categoria').addEventListener('click', async () => {
          const nomeCategoria = document.getElementById('id--nova--categoria').value;
          const iconeSelecionado = document.getElementById('icone-selecionado').value;

          if (nomeCategoria && iconeSelecionado) {
            const response = await fetch('../../config/conteudos/categorias/adicionar_categoria.php', { // Atualize o caminho para o seu script PHP
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({
                nome: nomeCategoria,
                icone: iconeSelecionado,
              }),
            });

            const responseText = await response.text(); // Recebe a resposta como texto
            console.log('Resposta do servidor:', responseText); // Log para depuração

            try {
              const result = JSON.parse(responseText); // Tenta converter para JSON

              // Verifica se a categoria foi adicionada com sucesso
              if (result.status === 'success') {
                alert('Categoria adicionada com sucesso!');
                // Aqui você pode fechar o popup e/ou atualizar a lista de categorias
              } else {
                alert('Erro ao adicionar categoria: ' + result.message);
              }
            } catch (error) {
              console.error('Erro ao analisar JSON:', error);
              alert('Erro inesperado ao adicionar categoria.');
            }
          } else {
            alert('Por favor, preencha todos os campos.');
          }
        });

        // Fecha o popup
        document.getElementById('id--fecha--btn--categoria').addEventListener('click', () => {
          document.getElementById('id--popup--categoria').style.display = 'none';
        });
      });
    </script>
</body>

</html>