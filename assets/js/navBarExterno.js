//###> Function para carregar um HTML EXTERNO
function carregandoNavBar(id, url) {
    fetch(url)
        .then(response => response.text())
        .then(data => document.getElementById(id).innerHTML = data)
        .catch(error => console.error('Erro ao carregar HTML:', error));
}

//###> Carregando passando os parametros 
carregandoNavBar('navbar','navbar.html')