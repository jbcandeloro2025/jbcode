<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualização - Gestão de NF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .color-rectangle {
            width: 100%;
            min-width: 80px;
            height: 20px;
            border-radius: 4px;
            border: 1px solid rgba(0,0,0,0.2);
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 0.25em 0.7em;
            border-radius: 12px;
            font-weight: 500;
            display: inline-block;
        }
        .status-entrada { background: #e0f2fe; color: #2563eb; }
        .status-preparo { background: #fef9c3; color: #92400e; }
        .status-transporte { background: #fef2f2; color: #be123c; }
        .status-desconhecido { background: #f3f4f6; color: #4b5563; }
    </style>
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
                            <a href="visualizacao.php" class="bg-indigo-100 text-indigo-700 px-3 py-2 rounded-md text-sm font-medium">Visualização</a>
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
        <div class="max-w-7xl mx-auto bg-white p-4 sm:p-6 rounded-xl shadow-lg">
            <h1 class="text-2xl font-bold mb-6">Visualização dos Envios</h1>
            
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NF</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Envio</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sub Cliente</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-corpo" class="bg-white divide-y divide-gray-200">
                    </tbody>
                </table>
            </div>

            <div id="cards-container" class="md:hidden space-y-4">
            </div>

            <div id="mensagem-tabela" class="text-center py-8 text-gray-500"></div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabelaCorpo = document.getElementById('tabela-corpo');
        const cardsContainer = document.getElementById('cards-container');
        const mensagemTabela = document.getElementById('mensagem-tabela');

        function statusBadge(status) {
            let cls = 'status-badge';
            if (status === "Entrada") cls += " status-entrada";
            else if (status === "Preparo") cls += " status-preparo";
            else if (status === "Transporte") cls += " status-transporte";
            else cls += " status-desconhecido";
            return `<span class="${cls}">${status || 'Desconhecido'}</span>`;
        }

        async function carregarDados() {
            mensagemTabela.textContent = 'Carregando dados...';
            try {
                const response = await fetch('api.php?acao=listar_registros_tabela');
                const data = await response.json();

                if (!response.ok) throw new Error(data.error);

                if (data.length === 0) {
                    mensagemTabela.textContent = 'Nenhum registro encontrado.';
                    return;
                }
                
                mensagemTabela.textContent = '';
                tabelaCorpo.innerHTML = '';
                cardsContainer.innerHTML = '';

                data.forEach(reg => {
                    console.log('Status do registro:', reg.status); // Depuração
                    let corDisplay;
                    const corPrimaria = reg.hex_primario;
                    const corSecundaria = reg.hex_secundario;

                    if (corSecundaria) {
                        corDisplay = `<div class="color-rectangle" style="background: linear-gradient(to right, ${corPrimaria} 75%, ${corSecundaria} 75%);" title="${reg.combinacao_nome}"></div>`;
                    } else {
                        corDisplay = `<div class="color-rectangle" style="background-color: ${corPrimaria};" title="${reg.combinacao_nome}"></div>`;
                    }

                    const linha = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${reg.data_formatada}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${corDisplay}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${reg.numero_nf}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${reg.envio_nome}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${reg.cliente_nome}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${reg.sub_cliente_nome || 'N/A'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${reg.tipo_produto_nome}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${reg.quantidade}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">${statusBadge(reg.status)}</td>
                        </tr>
                    `;
                    tabelaCorpo.innerHTML += linha;

                    const card = `
                        <div class="bg-gray-50 p-4 rounded-lg shadow">
                            <div class="flex justify-between items-start mb-2">
                                <div class="font-bold text-lg">NF: ${reg.numero_nf}</div>
                                <div class="text-sm text-gray-600">${reg.data_formatada}</div>
                            </div>
                            <div class="mb-3">${corDisplay}</div>
                            <div class="text-sm space-y-1">
                                <p><strong class="text-gray-600">Envio:</strong> ${reg.envio_nome}</p>
                                <p><strong class="text-gray-600">Cliente:</strong> ${reg.cliente_nome}</p>
                                <p><strong class="text-gray-600">Sub-Cliente:</strong> ${reg.sub_cliente_nome || 'N/A'}</p>
                                <p><strong class="text-gray-600">Tipo:</strong> ${reg.tipo_produto_nome}</p>
                                <p><strong class="text-gray-600">Quantidade:</strong> ${reg.quantidade}</p>
                                <p><strong class="text-gray-600">Status:</strong> ${statusBadge(reg.status)}</p>
                            </div>
                        </div>
                    `;
                    cardsContainer.innerHTML += card;
                });

            } catch (error) {
                mensagemTabela.textContent = `Erro ao carregar dados: ${error.message}`;
            }
        }

        carregarDados();
    });    
    </script>
</body>
</html>