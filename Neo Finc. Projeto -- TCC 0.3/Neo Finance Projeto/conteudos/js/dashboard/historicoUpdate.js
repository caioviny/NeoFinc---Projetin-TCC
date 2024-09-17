// Função para formatar o valor para o formato monetário
function formatarMoeda(valor) {
    return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

// Função para adicionar um item ao histórico
function adicionarAoHistorico(tipo, categoria, nome, valor, iconHTML) {
    var historicoList = document.getElementById('historicoList');
    var listItem = document.createElement('li');

    // Cria o conteúdo do item de histórico com as classes especificadas
    var historicoContent = `
        <div class="parte--um-info">
            <div class="img--categoria">${iconHTML}</div>
            <div class="info--detalhada">
                <span class="item--historico">${tipo}</span>
                <span class="categoria--historico">${categoria}</span>
            </div>
        </div>
        <div class="parte--dois-info">
            <span class="data--historico">${new Date().toLocaleDateString('pt-BR')}</span> <!-- Exibe a data atual -->
            <span class="valor--historico">${formatarMoeda(valor)}</span>
        </div>
    `;
    
    // Adiciona o conteúdo ao item de lista
    listItem.innerHTML = historicoContent;
    
    // Adiciona o item à lista de histórico
    historicoList.appendChild(listItem);
}
