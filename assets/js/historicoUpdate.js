function adicionarAoHistorico(tipo, categoria, nome, valor, iconHTML) {
    var historicoList = document.getElementById('historicoList');
    var listItem = document.createElement('li');

    // Cria o conteúdo do item de histórico
    var historicoContent = `
        <div class="historico">
            ${iconHTML} <strong>${tipo}</strong> - ${categoria} - ${nome}
        </div>
        <span>${formatarMoeda(valor)}</span>
    `;
    // Adiciona o conteúdo ao item de lista
    listItem.innerHTML = historicoContent;

    // Adiciona o item à lista de histórico
    historicoList.appendChild(listItem);
}