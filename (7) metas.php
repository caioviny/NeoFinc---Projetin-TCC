<?php
require_once '../../config/database/conexao.php';
session_start(); // Não se esqueça de iniciar a sessão

// Verifique se o usuário está autenticado e se 'user_id' está presente na sessão
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo "Erro: Usuário não autenticado.";
    exit();
}

$userId = $_SESSION['user_id'];

// Verificar se o usuário existe na tabela 'users'
$sql_check_user = "SELECT id FROM users WHERE id = ?";
$stmt_check_user = $conn->prepare($sql_check_user);
$stmt_check_user->bind_param("i", $userId);
$stmt_check_user->execute();
$stmt_check_user->store_result();

// Se não encontrar o usuário, interrompa a execução
if ($stmt_check_user->num_rows == 0) {
    echo "Erro: O usuário não existe.";
    exit();
}
$stmt_check_user->close();

// Verifique se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['goal_name'])) {
    // Recebe os dados do formulário
    $goalName = $_POST['goal_name'];
    $targetAmount = $_POST['target_amount'];
    $deadline = $_POST['deadline'];

    // Inserção de dados na tabela 'metas_usuario' (se esta for a tabela correta)
    $sql = "INSERT INTO metas_usuario (id_usuario, nome_meta, valor_alvo, prazo) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isds", $userId, $goalName, $targetAmount, $deadline);

    // Executar a inserção e redirecionar em caso de sucesso
    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Erro: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
}

// Exclusão de meta se 'id' for passado via GET
if (isset($_GET['id'])) {
    $goalId = $_GET['id'];

    // Exclusão da meta
    $sql = "DELETE FROM metas_usuario WHERE id_meta = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $goalId);

    // Executar a exclusão e redirecionar em caso de sucesso
    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Erro: " . $sql . "<br>" . $conn->error;
    }

    $stmt->close();
}

// Seleção de metas para exibição
$sql = "SELECT * FROM metas_usuario WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Minhas Metas</title>
</head>
<body>
    <h1>Minhas Metas</h1>

    <!-- Formulário para criação de meta -->
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <label for="goal_name">Nome da Meta:</label>
        <input type="text" id="goal_name" name="goal_name" required>

        <label for="target_amount">Valor Alvo:</label>
        <input type="number" id="target_amount" name="target_amount" required>

        <label for="deadline">Prazo:</label>
        <input type="date" id="deadline" name="deadline" required>

        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

        <input type="submit" value="Criar Meta">
    </form>

    <h2>Metas Criadas:</h2>
    <ul>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<li>{$row['nome_meta']} - Valor Alvo: {$row['valor_alvo']} - Prazo: {$row['prazo']} <a href='{$_SERVER['PHP_SELF']}?id={$row['id_meta']}'>Apagar</a></li>";
            }
        } else {
            echo "<li>Nenhuma meta criada ainda.</li>";
        }
        ?>
    </ul>
</body>
</html>
