<?php 
include("../../config/database/conexao.php");
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Metas</title>
  <link rel="stylesheet" href="../../css/conteudos/metas/metas.css">
  <link rel="stylesheet" href="../../css/conteudos/metas/popUpMetas.css">
</head>

<body>

  <div class="container">

    <!-- Menu Lateral -->
    <!-- Botão para abrir o popup -->
    <div class="menu-lateral" onclick="abrirModalAdicionar()">
      <div class="adicionar--btn">
        <img src="../../assets/icons/quadrado-adicionar.png" alt="add--btn">
      </div>
    </div>

    <!-- Cards de Metas -->
    <div class="container-cards">

      <!-- Card 1 -->
      <div class="card-meta">
        <div class="titulo-card">
          <span>Headset</span>
          <div class="iconeMeta">
          </div>
          <div class="icone-lixeira"><i class="fi fi-sr-trash"></i></div>
        </div>
        <div class="progresso-meta">
          <span>R$ 50,00</span>
          <div class="barra-progresso">
            <div class="barra-progresso-preenchida" style="width: 16.67%;"></div>
          </div>
          <span>de R$ 300,00</span>
        </div>
        <div class="botoes-meta">
          <button class="btn-depositar">
            <div for="icon2"><i class="fi fi-sr-home"></i></div> Depositar
          </button>
          <button class="btn-resgatar">
            <div for="icon2"><i class="fi fi-sr-home"></i></div> Resgatar
          </button>
          <button class="btn-historico">
            <div for="icon2"><i class="fi fi-sr-home"></i></div> Histórico
          </button>
        </div>

        <!-- Elemento para o gráfico -->
        <div class="grafico" id="chart" style="height: 100px; width: 100%;"></div>
      </div>


    </div>

    <!-- Botão de Avançar -->
    <div class="btn-avancar">
      >
    </div>


    <!-- POPUP METAS -->
<div class="pop-up-metas-container" id="pop-up-metas-container" style="display: none;">
  <div class="pop-up-metas-conteudo">
    <span class="popup-metas-close-btn" id="btn-fechar-popup-metas">&times;</span>
    <h2 class="metas-titulo">Crie uma meta</h2>

    <form action="salvar_meta.php" method="POST">
      <label for="nome">Meta:</label>
      <input type="text" name="nome" required>

      <label for="valor">Valor para alcançar meta:</label>
      <input type="text" id="valor" name="valor" required placeholder="0,00">

      <label for="data">Período:</label>
      <input type="date" id="data" name="data" required>

      <input type="hidden" name="usuario_id" value="<?php echo $_SESSION['usuario_id']; ?>"> <!-- Assumindo que o ID do usuário está na sessão -->

      <button type="submit">Adicionar meta</button>
    </form>
  </div>
</div>



    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        var options = {
    series: [75], // Porcentagem do gráfico
    chart: {
        height: 200, // A altura também pode ser definida aqui
        type: 'radialBar',
        toolbar: {
            show: true
        }
    },
    plotOptions: {
        radialBar: {
            startAngle: -135,
            endAngle: 225,
            hollow: {
                margin: 0,
                size: '60%', // Ajuste o tamanho do espaço vazio
                background: '#fff',
            },
            track: {
                background: '#fff',
                strokeWidth: '67%',
            },
            dataLabels: {
                show: true,
                name: {
                    offsetY: -10,
                    show: true,
                    color: '#888',
                    fontSize: '12px' // Reduzindo o tamanho da fonte
                },
                value: {
                    formatter: function (val) {
                        return parseInt(val);
                    },
                    color: '#111',
                    fontSize: '20px', // Reduzindo o tamanho da fonte
                    show: true,
                }
            }
        }
    },
    fill: {
        colors: ['#00e060'], // Aplicando a cor desejada
    },
    stroke: {
        lineCap: 'round'
    },
    labels: ['Porcentagem'],
};

      // Renderizando o gráfico
      var chart = new ApexCharts(document.querySelector("#chart"), options);
      chart.render();

    </script>


    <script src="../../js/conteudos/dashboard/formataMoeda.js"></script>
    <script>
      function abrirModalAdicionar() {
        document.getElementById('pop-up-metas-container').style.display = 'flex'; // Abre o popup
      }

      document.getElementById('btn-fechar-popup-metas').onclick = function () {
        document.getElementById('pop-up-metas-container').style.display = 'none'; // Fecha o popup
      };

      // Para fechar o popup clicando fora dele (opcional)
      window.onclick = function (event) {
        const popup = document.getElementById('pop-up-metas-container');
        if (event.target === popup) {
          popup.style.display = 'none'; // Fecha o popup
        }
      }


    </script>
</body>

</html>