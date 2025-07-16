<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastros - Gestão de NF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .delete-btn { cursor: pointer; color: #ef4444; }
        .delete-btn:hover { color: #dc2626; }
        .color-preview { width: 16px; height: 16px; border-radius: 4px; border: 1px solid #ccc; margin-right: 8px; flex-shrink: 0; }
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
                            <a href="visualizacao.php" class="text-gray-500 hover:bg-gray-200 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Visualização</a>
                            <a href="editar_notas.php" class="text-gray-500 hover:bg-gray-200 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Editar Notas</a>
                            <a href="cadastros.php" class="bg-indigo-100 text-indigo-700 px-3 py-2 rounded-md text-sm font-medium">Cadastros</a>
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
            <h1 class="text-2xl font-bold mb-6">Gerenciar Cadastros</h1>
            <div id="status-geral" class="mb-4 text-center font-medium"></div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h2 class="text-lg font-bold mb-4">Combinações de Cores</h2>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Combinações Cadastradas</label>
                        <select id="dropdown-combinacoes-cores" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="">Selecione para visualizar</option>
                        </select>
                    </div>
                    <form class="mb-4 p-3 border rounded-lg bg-gray-50" data-tabela="combinacoes_cores">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <input type="text" name="nome" placeholder="Nome da combinação" required class="sm:col-span-2 px-3 py-2 bg-white border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-600">Cor Primária</label>
                                <input type="color" name="hex_primario" value="#22c55e" class="w-full h-8 p-1 border border-gray-300 rounded-md">
                            </div>
                             <div class="space-y-1">
                                <label class="text-xs font-medium text-gray-600">Cor Secundária</label>
                                <div class="flex gap-2">
                                    <input type="color" name="hex_secundario" value="#000000" class="flex-1 h-8 p-1 border border-gray-300 rounded-md">
                                    <button type="submit" class="bg-indigo-600 text-white font-medium px-3 py-1 rounded-md hover:bg-indigo-700 text-sm">Salvar</button>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" id="sem_secundaria" name="sem_secundaria" class="h-3 w-3 text-indigo-600 border-gray-300 rounded">
                                    <label for="sem_secundaria" class="ml-1 block text-xs text-gray-700">Apenas cor primária</label>
                                </div>
                            </div>
                        </div>
                    </form>
                    <ul id="lista-combinacoes_cores" class="space-y-2"></ul>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h2 class="text-lg font-bold mb-4">Clientes</h2>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Clientes Cadastrados</label>
                        <select id="dropdown-clientes" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="">Selecione para visualizar</option>
                        </select>
                    </div>
                    <form class="mb-4 flex gap-2" data-tabela="clientes"><input type="text" name="nome" placeholder="Novo cliente" required class="flex-grow px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500"><button type="submit" class="bg-indigo-600 text-white font-semibold px-4 py-2 rounded-md hover:bg-indigo-700">Salvar</button></form>
                    <ul id="lista-clientes" class="space-y-2"></ul>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h2 class="text-lg font-bold mb-4">Sub Clientes</h2>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sub Clientes Cadastrados</label>
                        <select id="dropdown-sub-clientes" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="">Selecione para visualizar</option>
                        </select>
                    </div>
                    <form class="mb-4 space-y-2" data-tabela="sub_clientes"><select name="cliente_id" required class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500"></select><div class="flex gap-2"><input type="text" name="nome" placeholder="Novo sub-cliente" required class="flex-grow px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500"><button type="submit" class="bg-indigo-600 text-white font-semibold px-4 py-2 rounded-md hover:bg-indigo-700">Salvar</button></div></form>
                    <ul id="lista-sub_clientes" class="space-y-2"></ul>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h2 class="text-lg font-bold mb-4">Envios</h2>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Envios Cadastrados</label>
                        <select id="dropdown-envios" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="">Selecione para visualizar</option>
                        </select>
                    </div>
                    <form class="mb-4 flex gap-2" data-tabela="envios"><input type="text" name="nome" placeholder="Novo envio" required class="flex-grow px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500"><button type="submit" class="bg-indigo-600 text-white font-semibold px-4 py-2 rounded-md hover:bg-indigo-700">Salvar</button></form>
                    <ul id="lista-envios" class="space-y-2"></ul>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg">
                    <h2 class="text-lg font-bold mb-4">Tipos de Produto</h2>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipos Cadastrados</label>
                        <select id="dropdown-tipos-produto" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="">Selecione para visualizar</option>
                        </select>
                    </div>
                    <form class="mb-4 flex gap-2" data-tabela="tipos_produto"><input type="text" name="nome" placeholder="Novo tipo" required class="flex-grow px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500"><button type="submit" class="bg-indigo-600 text-white font-semibold px-4 py-2 rounded-md hover:bg-indigo-700">Salvar</button></form>
                    <ul id="lista-tipos_produto" class="space-y-2"></ul>
                </div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const statusGeral = document.getElementById('status-geral');

        function showStatus(message, isError = false) {
            statusGeral.textContent = message;
            statusGeral.className = `mb-4 text-center font-medium ${isError ? 'text-red-600' : 'text-green-600'}`;
            setTimeout(() => {
                statusGeral.textContent = '';
                statusGeral.className = 'mb-4 text-center font-medium';
            }, 3000);
        }

        function renderizarLista(containerId, items, subTextMap = null) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            if (items.length === 0) {
                container.innerHTML = '<li class="text-sm text-gray-500">Nenhum item cadastrado.</li>';
                return;
            }
            items.forEach(item => {
                let subText = '';
                if (subTextMap && item[subTextMap.key]) {
                    const parent = subTextMap.data.find(p => p.id == item[subTextMap.key]);
                    if (parent) subText = `<span class="text-xs text-gray-500 ml-2">(${parent.nome})</span>`;
                }
                
                let colorPreview = '';
                if(containerId === 'lista-combinacoes_cores') {
                    const colorRect = item.hex_secundario 
                        ? `<div class="color-preview" style="background: linear-gradient(to right, ${item.hex_primario} 50%, ${item.hex_secundario} 50%);"></div>`
                        : `<div class="color-preview" style="background-color: ${item.hex_primario};"></div>`;
                    colorPreview = colorRect;
                }

                container.innerHTML += `
                    <li class="flex justify-between items-center bg-gray-50 p-2 rounded-md text-sm">
                        <div class="flex items-center">${colorPreview}${item.nome} ${subText}</div>
                        <div class="delete-btn" data-id="${item.id}" data-tabela="${containerId.replace('lista-', '')}" title="Deletar">&#10006;</div>
                    </li>
                `;
            });
        }

        async function carregarCadastros() {
            try {
                const response = await fetch('api.php?acao=listar_cadastros');
                const data = await response.json();
                if (!response.ok) throw new Error(data.error);

                renderizarLista('lista-clientes', data.clientes);
                renderizarLista('lista-envios', data.envios);
                renderizarLista('lista-tipos_produto', data.tipos_produto);
                renderizarLista('lista-combinacoes_cores', data.combinacoes_cores);
                renderizarLista('lista-sub_clientes', data.sub_clientes, { key: 'cliente_id', data: data.clientes });

                // Preencher dropdown de combinações de cores
                const dropdownCombinacoesCores = document.getElementById('dropdown-combinacoes-cores');
                dropdownCombinacoesCores.innerHTML = '<option value="">Selecione para visualizar</option>';
                data.combinacoes_cores.forEach(combinacao => {
                    dropdownCombinacoesCores.innerHTML += `<option value="${combinacao.id}">${combinacao.nome}</option>`;
                });

                // Preencher dropdown de clientes
                const dropdownClientes = document.getElementById('dropdown-clientes');
                dropdownClientes.innerHTML = '<option value="">Selecione para visualizar</option>';
                data.clientes.forEach(cliente => {
                    dropdownClientes.innerHTML += `<option value="${cliente.id}">${cliente.nome}</option>`;
                });

                // Preencher dropdown de sub clientes
                const dropdownSubClientes = document.getElementById('dropdown-sub-clientes');
                dropdownSubClientes.innerHTML = '<option value="">Selecione para visualizar</option>';
                data.sub_clientes.forEach(subCliente => {
                    const clientePai = data.clientes.find(c => c.id == subCliente.cliente_id);
                    const clientePaiNome = clientePai ? clientePai.nome : 'Cliente não encontrado';
                    dropdownSubClientes.innerHTML += `<option value="${subCliente.id}">${subCliente.nome} (${clientePaiNome})</option>`;
                });

                // Preencher dropdown de envios
                const dropdownEnvios = document.getElementById('dropdown-envios');
                dropdownEnvios.innerHTML = '<option value="">Selecione para visualizar</option>';
                data.envios.forEach(envio => {
                    dropdownEnvios.innerHTML += `<option value="${envio.id}">${envio.nome}</option>`;
                });

                // Preencher dropdown de tipos de produto
                const dropdownTiposProduto = document.getElementById('dropdown-tipos-produto');
                dropdownTiposProduto.innerHTML = '<option value="">Selecione para visualizar</option>';
                data.tipos_produto.forEach(tipo => {
                    dropdownTiposProduto.innerHTML += `<option value="${tipo.id}">${tipo.nome}</option>`;
                });

                const clienteSelect = document.querySelector('form[data-tabela="sub_clientes"] select[name="cliente_id"]');
                clienteSelect.innerHTML = '<option value="">Selecione o cliente pai</option>';
                data.clientes.forEach(c => {
                    clienteSelect.innerHTML += `<option value="${c.id}">${c.nome}</option>`;
                });

            } catch (error) {
                showStatus(`Erro: ${error.message}`, true);
            }
        }

        document.body.addEventListener('submit', async function(e) {
            if (e.target.tagName === 'FORM' && e.target.closest('.bg-white')) {
                e.preventDefault();
                const form = e.target;
                const tabela = form.dataset.tabela;
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                data.tabela = tabela;
                
                if (tabela === 'combinacoes_cores' && data.sem_secundaria === 'on') {
                    data.hex_secundario = null;
                }

                try {
                    const response = await fetch('api.php?acao=adicionar_item', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.error);
                    
                    form.reset();
                    showStatus(result.success);
                    carregarCadastros();
                } catch (error) {
                    showStatus(`Erro: ${error.message}`, true);
                }
            }
        });

        document.body.addEventListener('click', async function(e) {
            if (e.target.classList.contains('delete-btn')) {
                if (!confirm('Tem certeza que deseja deletar este item?')) return;
                
                const id = e.target.dataset.id;
                const tabela = e.target.dataset.tabela;

                try {
                    const response = await fetch('api.php?acao=deletar_item', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id, tabela })
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.error);

                    showStatus(result.success);
                    carregarCadastros();
                } catch (error) {
                    showStatus(`Erro: ${error.message}`, true);
                }
            }
        });

        carregarCadastros();
    }); </script>
</body>
</html>

