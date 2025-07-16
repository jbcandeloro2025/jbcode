<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestão Logística</title>
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
                            <a href="dashboard.php" class="bg-indigo-100 text-indigo-700 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <a href="index.php" class="text-gray-500 hover:bg-gray-200 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Novo Registro</a>
                            <a href="visualizacao.php" class="text-gray-500 hover:bg-gray-200 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Visualização</a>
                            <a href="editar_notas.php" class="text-gray-500 hover:bg-gray-200 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Editar Notas</a>
                            <a href="cadastros.php" class="text-gray-500 hover:bg-gray-200 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Cadastros</a>
                             <?php if (isAdmin()): ?>
                                <a href="gerenciar_usuarios.php" class="text-gray-500 hover:bg-gray-200 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Usuários</a>
                                <a href="logs.php" class="text-gray-500 hover:bg-gray-200 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Logs</a>
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
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-bold mb-6">Dashboard</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h2 class="text-lg font-bold mb-2">Acesso Rápido</h2>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-indigo-600 hover:underline">Criar um novo registro</a></li>
                        <li><a href="visualizacao.php" class="text-indigo-600 hover:underline">Ver todos os registros</a></li>
                        <li><a href="cadastros.php" class="text-indigo-600 hover:underline">Gerenciar cadastros base</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

</body>
</html>

