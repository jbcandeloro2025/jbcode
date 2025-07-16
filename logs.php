<?php
require_once 'auth.php';
if (!isAdmin()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs do Sistema - Gestão Logística</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                 <div class="flex items-center">
                    <div class="flex-shrink-0 font-bold text-indigo-600">Gestão Logística</div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="dashboard.php" class="text-gray-500 hover:bg-gray-200 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <a href="index.php" class="text-gray-500 hover:bg-gray-200 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Novo Registro</a>
                            <a href="visualizacao.php" class="text-gray-500 hover:bg-gray-200 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Visualização</a>
                            <a href="editar_notas.php" class="text-gray-500 hover:bg-gray-200 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Editar Notas</a>
                            <a href="cadastros.php" class="text-gray-500 hover:bg-gray-200 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Cadastros</a>
                             <?php if (isAdmin()): ?>
                                <a href="gerenciar_usuarios.php" class="text-gray-500 hover:bg-gray-200 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Usuários</a>
                                <a href="logs.php" class="bg-indigo-100 text-indigo-700 px-3 py-2 rounded-md text-sm font-medium">Logs</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <span class="text-gray-700 text-sm">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></span>
                    <a href="api.php?acao=logout" class="bg-red-500 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-red-600">Sair</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="p-4 sm:p-6 md:p-8">
        <div class="max-w-7xl mx-auto bg-white p-6 rounded-xl shadow-lg">
            <h1 class="text-2xl font-bold mb-6">Logs do Sistema</h1>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data/Hora</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Usuário</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ação</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Detalhes</th>
                        </tr>
                    </thead>
                    <tbody id="lista-logs" class="bg-white divide-y divide-gray-200"></tbody>
                </table>
                <div id="status-lista" class="text-center py-4 text-gray-500"></div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        carregarLogs();
    });

    async function carregarLogs() {
        const listaLogs = document.getElementById('lista-logs');
        const statusLista = document.getElementById('status-lista');
        
        statusLista.textContent = 'Carregando logs...';
        try {
            const response = await fetch('api.php?acao=listar_logs');
            const data = await response.json();
            if (!response.ok) throw new Error(data.error);
            
            listaLogs.innerHTML = '';
            if (data.length === 0) {
                statusLista.textContent = 'Nenhum log encontrado.';
                return;
            }
            statusLista.textContent = '';
            
            data.forEach(log => {
                listaLogs.innerHTML += `
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${log.timestamp}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${log.usuario_nome || 'N/A'}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${log.acao}</td>
                        <td class="px-4 py-2 text-sm text-gray-500">${log.detalhes || 'N/A'}</td>
                    </tr>`;
            });
        } catch (error) {
            statusLista.textContent = `Erro: ${error.message}`;
        }
    }
    </script>
</body>
</html>


