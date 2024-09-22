<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Neo Finance - Dashboard</title>
  <link rel="stylesheet" href="./css/dashboard/dashboard.css" />
  <link rel="stylesheet" href="./css/dashboard/popUp.css">

  <link rel='stylesheet'
    href='https://cdn-uicons.flaticon.com/2.5.1/uicons-solid-straight/css/uicons-solid-straight.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.5.1/uicons-solid-rounded/css/uicons-solid-rounded.css'>
  <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.5.1/uicons-bold-rounded/css/uicons-bold-rounded.css'>

</head>

<body>
<div class="conteudo" id="conteudo">
  <!-- Início Header -->
  <div class="container--header">
    <header class="perfil">
      <div class="usuario">
        <img src="./assets/Avatar.svg" alt="user--icon" />
        <!-- Nome do usuário -->
        <h1>Hello, Eneas Sukermman!</h1>
      </div>
      <div class="notificacao--usuario">
        <img src="./assets/sino--icon.svg" alt="icon-notificacao" />
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
          <h1 id="balanco--valor--total">$ 0,00</h1>
          <!-- Botões do balanço total -->
          <!-- <div class="botoes">
         <button class="botao">
           <img src="./assets/botao--adicionar.svg" alt="Adicionar Metas" />
           <span>Metas</span>
         </button>
         <button class="botao">
           <img src="./assets/botao--adicionar.svg" alt="Adicionar Metas" />
           <span>Metas</span>
         </button>
         <button class="botao">
           <img src="./assets/botao--adicionar.svg" alt="Adicionar Metas" />
           <span>Metas</span>
         </button>
       </div> -->
        </div>
        <!-- Fim Lado Esquerdo do Card -->

        <!-- Lado Direito do Card -->
        <div class="lado--direito-geral-bt">
          <div class="lado--direito-bt">
            <div class="parte--cima-bt">
              <div class="info--valores">
                <span>Saldo</span>
                <span id="resultado--receita">$ 0,00</span>
              </div>
              <img src="../assets/home/setinha--up--animcao.webp" alt="icon--saldo" class="icone--saldo--animacao" />
            </div>
            <div class="parte--baixo-bt">
              <div class="info--valores">
                <span>Gastos</span>
                <span id="resultado--despesa">$ 0,00</span>
              </div>
              <img src="./assets/icon--gastos.svg" alt="icon--gastos" />
            </div>
          </div>
          <!-- Botão Adicionar -->
          <div class="botao--adicionar">

            <img id="btn--abrir--popup" src="./assets/botao--adicionar.svg" alt="Adicionar" />

          </div>
        </div>
      </div>
      <!-- Fim Card Balanço Total -->

      <!-- fix: ajeitar formatação do historico -->
      <!-- Card Histórico Recente -->
      <div class="card--historico-recente">
        <div class="header--card-hr">
          <span>Histórico Recente</span>
          <button>Ver tudo</button>
        </div>
        <!-- Histórico de Transações -->
        <div class="info--historico">
          <ul id="historicoList">
            <!-- Itens do histórico serão adicionados aqui -->
          </ul>
        </div>
        <div class="seta--pra--baixo">

        </div>
      </div>
      <!-- Fim Histórico Recente -->



      <!-- Card Receitas x Despesas -->
      <div class="card--receitasXdespesas">
        <!-- Lado Esquerdo do Card -->
        <div class="lado--esquerdo-rd">
          <span>Receitas x Despesas</span>
          <div class="grafico--receitasXdespesas">
            <div class="grafico--receitas"></div>
            <!-- Gráfico de Receitas -->
            <div class="grafico--despesas"></div>
            <!-- Gráfico de Despesas -->
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
              <span>$ 5.500,00</span>
            </div>
          </div>
          <div class="despesas--filtro">
            <div class="icon--vermelho"></div>
            <div class="info--valores">
              <span>Despesas</span>
              <span>$ 5.500,00</span>
            </div>
          </div>
          <div class="saldo--filtro">
            <div class="icon--verde-claro"></div>
            <div class="info--valores">
              <span>Saldo</span>
              <span>$ 5.500,00</span>
            </div>
          </div>
        </div>
      </div>
      <!-- Fim Card Receitas x Despesas -->

      <!-- Card Próximos Vencimentos -->
      <div class="card--vencimentos">
        <div class="header--card-v">
          <div class="titulo--header-v">
            <img src="./assets/icon--calendario.svg" alt="icon--calendario" />
            <span>Vence hoje</span>
          </div>
          <span class="mes--vencimento">Dezembro</span>
        </div>
        <div class="info--vencimentos">
          <div class="info--descricao">
            <span class="data--vencimento">31</span>
            <div class="descricao--vencimento">
              <span>A pagar</span>
              <span>Fatura do Cartão</span>
            </div>
          </div>
          <div class="linha--vertical-v"></div>
          <span class="valor--vencimento">$ 90,00</span>
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



  <!-- ##### POPUP DE ADIÇÃO DE RECEITA/DESPESA ##### -->
  <div id="id--popup" class="popup--box">
    <div class="popup--conteudo">
      <span class="fecha--btn" id="id--fecha--btn">&times;</span>
      <h2>Adicionar Receita/Despesa</h2>
      <div class="form--grupo--tipo">
        <div class="form--item--tipo">
          <label for="label--tipo">Tipo</label>
          <select name="tipo" id="id--tipo">
            <option value="Receita">Receita</option>
            <option value="Despesa">Despesa</option>
          </select>
        </div>
        <div class="form-item--categoria">
          <label for="label--categoria">Categoria</label>
          <div class="grupo--input">
            <input type="text" placeholder="Digite a nova categoria" id="id--categoria">
            <button id="add--tipo--categoria" type="button">+</button>
          </div>
        </div>
        <div class="form--item--categoria--select">
          <label for="label--select--categoria">Categorias</label>
          <select id="categoria--select">
            <!-- As opções serão adicionadas dinamicamente aqui -->
          </select>
        </div>
      </div>
      <div class="form--grupo--nome">
        <div class="form--item--nome">
          <label for="label--nome">Nome</label>
          <input type="text" placeholder="Nome" id="id--nome">
        </div>
        <div class="form-item--valor">
          <label for="label--valor">Valor</label>
          <input type="number" placeholder="Valor" id="id--valor" step="0.01">
        </div>
      </div>
      <div class="form-item--icone">
        <label for="label--icone">Ícone</label>
        <button id="abrir--selecao--icones" type="button">Escolher Ícone</button>
        <!-- Exibe o ícone selecionado -->
        <div id="selecao--icone--container">
          <!-- Ícone selecionado será exibido aqui -->
        </div>
      </div>

      <div class="botao--componente">
        <button id="btn--enviar" onclick="subValores()">Enviar</button>
      </div>
    </div>
  </div>

  <!-- ##### POPUP DE SELEÇÃO DE ICONES ##### -->
  <div id="id--selecao--icones" class="selecao--icones--popup">
    <div class="icon--popup--conteudo">
      <span class="icon-fecha-btn" id="id--fecha--icone">&times;</span>
      <h3>Escolha um Ícone</h3>
      <div class="icone--grade">
        <!-- ##### ICONES ##### -->
        <div class="icon" data-icon="icon1"><i class="fi fi-br-scissors"></i></div>
        <div class="icon" data-icon="icon2"><i class="fi fi-sr-home"></i></i></div>
        <div class="icon" data-icon="icon3"><i class="fi fi-br-smartphone"></i></div>
        <div class="icon" data-icon="icon4"><i class="fi fi-sr-file-invoice-dollar"></i>
        </div>
        <div class="icon" data-icon="icon5"><i class="fi fi-br-money-coin-transfer"></i>
        </div>
        <div class="icon" data-icon="icon6"><i class="fi fi-ss-plane-alt"></i></div>
        <div class="icon" data-icon="icon7"><i class="fi fi-ss-bus-alt"></i></div>
        <div class="icon" data-icon="icon8"><i class="fi fi-ss-wrench-alt"></i></div>
        <div class="icon" data-icon="icon9"><i class="fi fi-ss-car-mechanic"></i></div>
        <div class="icon" data-icon="icon11"><i class="fi fi-sr-shopping-cart"></i></div>
        <div class="icon" data-icon="icon12"><i class="fi fi-sr-wallet"></i></div>
        <div class="icon" data-icon="icon13"><i class="fi fi-sr-gamepad"></i></div>
        <div class="icon" data-icon="icon14"><i class="fi fi-ss-hotdog"></i></div>
        <div class="icon" data-icon="icon15"><i class="fi fi-ss-user-md"></i></div>
        <div class="icon" data-icon="icon16"><i class="fi fi-sr-dog-leashed"></i></div>
        <div class="icon" data-icon="icon17"><i class="fi fi-sr-bone"></i></div>
        <div class="icon" data-icon="icon18"><i class="fi fi-sr-cat"></i></div>
        <div class="icon" data-icon="icon19"><i class="fi fi-sr-devices"></i></div>
        <div class="icon" data-icon="icon20"><i class="fi fi-ss-book-alt"></i></div>
        <div class="icon" data-icon="icon21"><i class="fi fi-sc-headphones"></i></div>
        <div class="icon" data-icon="icon22"><i class="fi fi-sc-music-alt"></i></div>
        <div class="icon" data-icon="icon23"><i class="fi fi-sc-speaker"></i></div>
        <div class="icon" data-icon="icon24"><i class="fi fi-sc-microphone-alt"></i></div>
      </div>
    </div>
  </div>


  <!-- Script Linkado (Sem Conteúdo) -->
  <script src="./js/dashboard/addCategorias.js"></script>
  <script src="./js/dashboard/envioBtnAdd.js"></script>
  <script src="./js/dashboard/historicoUpdate.js"></script>
  <script src="./js/dashboard/openPopUp.js"></script>
</body>

</html>