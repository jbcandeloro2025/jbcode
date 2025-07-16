<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gestão Logística</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-md">
        <div class="bg-white p-8 rounded-xl shadow-lg">
            <h1 class="text-3xl font-bold text-center text-indigo-600 mb-2">Gestão Logística</h1>
            <p class="text-center text-gray-500 mb-8">Faça login para continuar</p>
            <form id="form-login" class="space-y-6">
                <div>
                    <label for="nome" class="block text-sm font-medium text-gray-700">Nome de Usuário</label>
                    <input type="text" id="nome" name="nome" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="senha" class="block text-sm font-medium text-gray-700">Senha</label>
                    <input type="password" id="senha" name="senha" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    Entrar
                </button>
                <div id="mensagem-status" class="mt-4 text-sm text-center font-medium h-4 text-red-600"></div>
            </form>
        </div>
        <div class="text-center mt-4">
            <a href="registrar.php" class="text-sm text-indigo-600 hover:underline">Não tem uma conta? Registre-se</a>
        </div>
    </div>
    <script>
    document.getElementById('form-login').addEventListener('submit', async (e) => {
        e.preventDefault();
        const statusMessage = document.getElementById('mensagem-status');
        statusMessage.textContent = '';

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('api.php?acao=login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.error);
            
            window.location.href = 'dashboard.php';

        } catch (error) {
            statusMessage.textContent = error.message;
        }
    });
    </script>
</body>
</html>