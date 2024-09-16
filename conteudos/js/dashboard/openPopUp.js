document.getElementById('openPopupBtn').addEventListener('click', function () {
    document.getElementById('popup').style.display = 'block';
});

document.getElementById('closePopupBtn').addEventListener('click', function () {
    document.getElementById('popup').style.display = 'none';
});

document.getElementById('openIconSelectionPopupBtn').addEventListener('click', function () {
    document.getElementById('iconSelectionPopup').style.display = 'flex';
});

document.getElementById('closeIconSelectionPopupBtn').addEventListener('click', function () {
    document.getElementById('iconSelectionPopup').style.display = 'none';
});

document.querySelectorAll('#iconSelectionPopup .icon').forEach(function (iconElement) {
    iconElement.addEventListener('click', function () {
        // Atualizar o HTML do ícone selecionado
        selectedIconHTML = iconElement.querySelector('i').outerHTML;
        updateSelectedIcon(selectedIconHTML); // Atualiza a variável e o contêiner do ícone

        // Fechar o popup após a seleção
        document.getElementById('iconSelectionPopup').style.display = 'none';
    });
});

function updateSelectedIcon(iconHTML) {
    document.getElementById('selectedIconContainer').innerHTML = iconHTML; // Atualiza o botão de ícone
}