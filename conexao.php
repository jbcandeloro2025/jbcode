<?php
$host = getenv("DB_HOST") ?: "localhost";
$dbname = getenv("DB_NAME") ?: "gerenciador_logistico";
$username = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASSWORD") ?: "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Em um ambiente de produção, você não deveria expor detalhes do erro.
    // Logue o erro em um arquivo e mostre uma mensagem genérica.
    error_log("Erro de conexão com o banco de dados: " . $e->getMessage());
    // Para o usuário, uma mensagem genérica é mais segura.
    die("Não foi possível conectar ao banco de dados. Por favor, tente novamente mais tarde.");
}
?>