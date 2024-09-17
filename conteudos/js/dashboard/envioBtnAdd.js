var totalReceitas = 0;
var totalDespesas = 0;
var selectedIconHTML = ''; // Variável para armazenar o ícone selecionado

function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2
    }).format(valor);
}

function subValores() {
    var inputValor = document.getElementById('id--valor');
    var valor = parseFloat(inputValor.value.trim());
    var tipo = document.getElementById('id--tipo').value;
    var categoria = document.getElementById('categoria--select').value;
    var nome = document.getElementById('id--nome').value;
    var resultado = document.getElementById('balanco--valor--total');
    var resultadoReceita = document.getElementById('resultado--receita').querySelector('span');
    var resultadoDespesa = document.getElementById('resultado--despesa').querySelector('span');

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
        resultado.innerText = formatarMoeda(balancoTotal);

        // Salva os dados no histórico
        adicionarAoHistorico(tipo, categoria, nome, valor, selectedIconHTML);
    }
}