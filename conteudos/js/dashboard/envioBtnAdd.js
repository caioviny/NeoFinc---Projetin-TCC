var totalReceitas = 0;
var totalDespesas = 0;

// Função para formatar moeda
function formatarMoeda(valor) {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2
  }).format(valor);
}

// Função de submissão dos valores
function subValores() {
  var inputValor = document.getElementById('id--valor');
  var valor = parseFloat(inputValor.value.trim());
  var tipo = document.getElementById('id--tipo').value;
  var categoria = document.getElementById('categoria--select').value;
  var nome = document.getElementById('id--nome').value;
  
  var iconHTML = document.getElementById('selecao--icone--container').innerHTML; // Obtém o ícone selecionado

  var resultadoBalancoTotal = document.getElementById('balanco--valor--total'); // Corrigido para pegar o h1
  var resultadoReceita = document.getElementById('resultado--receita');
  var resultadoDespesa = document.getElementById('resultado--despesa');

  if (!isNaN(valor)) {
    if (tipo === "Receita") {
      totalReceitas += valor;
      resultadoReceita.innerText = formatarMoeda(totalReceitas);
    } else if (tipo === "Despesa") {
      totalDespesas += valor;
      resultadoDespesa.innerText = formatarMoeda(totalDespesas);
    }

    // Atualiza o balanço total
    var balancoTotal = totalReceitas - totalDespesas;
    resultadoBalancoTotal.innerText = formatarMoeda(balancoTotal);

    // Salva os dados no histórico
    adicionarAoHistorico(tipo, categoria, nome, valor, iconHTML);
  }
}
