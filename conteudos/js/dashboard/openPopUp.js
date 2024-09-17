// Abrir o popup de adição de receita/despesa
document.getElementById('btn--abrir--popup').addEventListener('click', function () {
    document.getElementById('id--popup').style.display = 'block'; // Abre o popup principal
});

// Fechar o popup de adição de receita/despesa
document.getElementById('id--fecha--btn').addEventListener('click', function () {
    document.getElementById('id--popup').style.display = 'none'; // Fecha o popup principal
});

// Abrir o popup de seleção de ícones
document.getElementById('abrir--selecao--icones').addEventListener('click', function () {
    document.getElementById('id--selecao--icones').style.display = 'flex'; // Abre o popup de seleção de ícones
});

// Fechar o popup de seleção de ícones
document.getElementById('id--fecha--icone').addEventListener('click', function () {
    document.getElementById('id--selecao--icones').style.display = 'none'; // Fecha o popup de seleção de ícones
});

// Lógica de seleção de ícones
document.querySelectorAll('#id--selecao--icones .icon').forEach(function (iconElement) {
    iconElement.addEventListener('click', function () {
        // Atualizar o HTML do ícone selecionado
        var selectedIconHTML = iconElement.querySelector('i').outerHTML;
        updateSelectedIcon(selectedIconHTML); // Atualiza o botão com o ícone selecionado

        // Fechar o popup de seleção de ícones após a seleção
        document.getElementById('id--selecao--icones').style.display = 'none';
    });
});

// Função para atualizar o ícone selecionado no container
function updateSelectedIcon(iconHTML) {
    document.getElementById('selecao--icone--container').innerHTML = iconHTML; // Atualiza o ícone exibido
}
