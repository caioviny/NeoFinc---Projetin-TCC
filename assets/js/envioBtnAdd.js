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
    var inputValor = document.getElementById('valor');
    var valor = parseFloat(inputValor.value.trim());
    var tipo = document.getElementById('tipo').value;
    var categoria = document.getElementById('categoriaSelect').value;
    var nome = document.getElementById('nome').value;
    var resultado = document.getElementById('resultado');
    var resultadoReceita = document.getElementById('resultadoReceita').querySelector('h1');
    var resultadoDespesa = document.getElementById('resultadoDespesa').querySelector('h1');

    if (!isNaN(valor)) {
        if (tipo === "receita") {
            totalReceitas += valor;
            resultadoReceita.innerText = formatarMoeda(totalReceitas);
        } else if (tipo === "despesa") {
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