   // Função para adicionar uma nova categoria
   function addCategory() {
    var categoriaInput = document.getElementById('id--categoria');
    var categoria = categoriaInput.value.trim();

    if (categoria !== '') {
        var option = document.createElement('option');
        option.value = categoria;
        option.text = categoria;

        var select = document.getElementById('categoria--select');
        select.add(option);

        categoriaInput.value = ''; // Limpa o campo de entrada
    } else {
        alert('Por favor, insira uma categoria.');
    }
}

// Adiciona a categoria ao clicar no botão
document.getElementById('add--tipo--categoria').addEventListener('click', addCategory);

// Adiciona a categoria ao pressionar Enter
document.getElementById('id--categoria').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault(); // Evita o comportamento padrão do Enter
        addCategory();
    }
});