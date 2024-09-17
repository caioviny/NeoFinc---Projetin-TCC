window.onload = function() {
    document.getElementById('id--popup').style.display = 'none'; // Inicialmente esconde o popup principal
    document.getElementById('id--selecao--icones').style.display = 'none'; // Inicialmente esconde o popup de ícones

    // Função para selecionar o ícone e fechar o popup de seleção de ícones
    document.querySelectorAll('#id--selecao--icones .icon').forEach(function(iconElement) {
        iconElement.addEventListener('click', function() {
            // Obtém o HTML do ícone selecionado
            var selectedIconHTML = iconElement.querySelector('i').outerHTML;

            // Atualiza o ícone selecionado no container do popup principal
            document.getElementById('selecao--icone--container').innerHTML = selectedIconHTML;

            // Fecha o popup de seleção de ícones
            document.getElementById('id--selecao--icones').style.display = 'none';
        });
    });
};

// Função para abrir o popup de adição de receita/despesa
document.getElementById('btn--abrir--popup').addEventListener('click', function() {
    document.getElementById('id--popup').style.display = 'block'; // Exibe o popup principal
});

// Função para fechar o popup de adição de receita/despesa
document.getElementById('id--fecha--btn').addEventListener('click', function() {
    document.getElementById('id--popup').style.display = 'none'; // Esconde o popup principal
});

// Função para abrir o popup de seleção de ícones somente quando o botão for clicado
document.getElementById('abrir--selecao--icones').addEventListener('click', function() {
    var popupSelecaoIcones = document.getElementById('id--selecao--icones');
    if (popupSelecaoIcones.style.display === 'none' || popupSelecaoIcones.style.display === '') {
        popupSelecaoIcones.style.display = 'block'; // Exibe o popup de seleção de ícones
    }
});

// Função para fechar o popup de seleção de ícones
document.getElementById('id--fecha--icone').addEventListener('click', function() {
    document.getElementById('id--selecao--icones').style.display = 'none'; // Esconde o popup de seleção de ícones
});

