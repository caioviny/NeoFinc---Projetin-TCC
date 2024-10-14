<?php
include("../../config/database/conexao.php");

// Verifica se a sessão já está ativa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../views/login/login.php");
    exit();
}

// Obter o ID do usuário logado
$user_id = $_SESSION['user_id'];

// Verificar se o saldo inicial já foi adicionado (considerando que o saldo esteja na tabela 'transacoes')
$query = "SELECT COUNT(*) AS saldo_inicial_adicionado FROM transacoes WHERE usuario_id = ? AND nome = 'Saldo Inicial'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$saldo_inicial_adicionado = $row['saldo_inicial_adicionado'] > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar o valor inserido pelo usuário e adicionar como saldo inicial
    $saldo_inicial = isset($_POST['saldo_inicial']) ? $_POST['saldo_inicial'] : 0.00;

    // Inserir a transação de saldo inicial na tabela de transações
    $query = "INSERT INTO transacoes (usuario_id, tipo, categoria_id, nome, valor) VALUES (?, 'Receita', 0, 'Saldo Inicial', ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("id", $user_id, $saldo_inicial);
    $stmt->execute();

    // Redirecionar para evitar reenvio do formulário
    header("Location: ../../../views/home.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neo Finance</title>
    <link rel="stylesheet" href="../../../css/dashboard/primeiro_login.css">
</head>
<style>
@import url(../../root.css);

/* Estilo para o container do popup */
.popup-container-bem-vindo {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: rgba(0, 0, 0, 0.7);
  z-index: 9999;
}

/* Estilo para o popup de boas-vindas */
.popup-bem-vindo {
  background-color: var(--verde--vidro);
  padding: 40px;
  border-radius: 12px;
  width: 900px;
  max-width: 95%;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
  font-family: var(--fonte-principal);
  border: 2px solid var(--cor--destaque-verde);
  color: var(--fonte-branco-100);
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  max-height: 90vh;
  overflow-y: auto;
  z-index: 9999;
}


/* Estilo para o título do popup de boas-vindas */
.popup-bem-vindo h2 {
  color: var(--cor--destaque-verde);
  margin-bottom: 20px;
  font-size: 24px;
  text-align: center;
  font-weight: 600;
}

/* Estilo para os parágrafos dentro do popup */
.popup-bem-vindo p {
  color: white;
  margin-bottom: 15px;
  text-align: center;
}

/* Estilo para os rótulos dentro do popup */
.popup-bem-vindo label {
  color: white;
  display: block;
  margin-top: 15px;
  font-size: 15px;
  font-weight: 500;
}

/* Estilo para os inputs dentro do popup */
.popup-bem-vindo input[type="text"] {
  width: 100%;
  padding: 12px 20px;
  margin-top: 5px;
  background-color: var(--verde--vidro);
  color: var(--fonte-branco-100);
  border: none;
  border-radius: 4px;
  box-sizing: border-box;
  transition: all 0.3s ease;
}

/* Foco no input */
.popup-bem-vindo input[type="number"]:focus {
  outline: none;
  background-color: var(--cor--destaque-verde);
  border: 2px solid var(--cor--destaque-verde);
}

/* Estilo para o botão de envio dentro do popup */
.popup-bem-vindo button {
  background-color: var(--cor--destaque-verde);
  color: var(--fonte-branco-100);
  border: none;
  padding: 12px 25px;
  margin-top: 25px;
  width: 100%;
  border-radius: 6px;
  cursor: pointer;
  font-size: 18px;
  font-weight: 600;
  transition: background-color 0.3s ease, transform 0.2s;
}

/* Hover no botão dentro do popup */
.popup-bem-vindo button:hover {
  background-color: var(--hover);
  transform: scale(1.05);
}

/* Close button estilizado para o popup */
.close-btn-primeiroLogin {
  position: absolute;
  top: 12px;
  right: 12px;
  font-size: 24px;
  color: var(--fonte-branco-100);
  cursor: pointer;
  padding: 0;
  background: none;
  border: none;
  transition: color 0.3s ease;
}

/* Hover no botão de fechar */
.close-btn-primeiroLogin:hover {
  color: var(--cor--destaque-verde);
}


</style>
<body>
    <?php if (!$saldo_inicial_adicionado): ?>
    <!-- Popup de Boas-Vindas -->
    <div id="popup-bem-vindo-usuario" class="popup-container-bem-vindo">
        <div class="popup-bem-vindo">
            <h2>Bem-vindo ao Neo Finance <?php echo $_SESSION['username']; ?></h2>
            <p>Para começarmos, informe se tem um saldo atual que gostaria de adicionar.</p>
            <form action="" method="POST">
                <label for="saldo_inicial">Saldo Inicial (R$):</label>
                <input type="text" name="saldo_inicial" id="saldo_inicial" required placeholder="0,00">
                
                <button type="submit">Adicionar Saldo</button>
            </form>
            <!-- Botão para fechar o popup -->
            <div class="close-btn-primeiroLogin" onclick="document.getElementById('popup-bem-vindo-usuario').style.display = 'none';">&times;</div>
        </div>
    </div>
    <script>
        // Exibir o popup automaticamente
        document.getElementById('popup-bem-vindo-usuario').style.display = 'block';
    </script>
    <?php endif; ?>
</body>
<script>
  function formatarMoeda(valor) {
    // Remove todos os caracteres que não são dígitos
    valor = valor.replace(/\D/g, "");

    // Limita o valor a 8 dígitos antes da vírgula (999.999,99)
    if (valor.length > 8) {
      valor = valor.slice(0, 8); // Limita a 8 caracteres (6 dígitos inteiros + 2 decimais)
    }

    // Formata para moeda
    let valorFormatado = (valor / 100)
      .toFixed(2) // Converte para decimal e fixa em 2 casas decimais
      .replace(".", ",") // Substitui o ponto decimal pela vírgula
      .replace(/\B(?=(\d{3})+(?!\d))/g, "."); // Adiciona pontos para os milhares

    return valorFormatado;
  }

  // Evento de digitação
  document.getElementById("saldo_inicial").addEventListener("input", function () {
    let valorAtual = this.value;

    // Remove formatação e formata novamente
    this.value = formatarMoeda(valorAtual.replace(/\D/g, ""));
  });
</script>
</html>
