<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Notas - Gestão de NF</title>
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
                            <a href="editar_notas.php" class="bg-indigo-100 text-indigo-700 px-3 py-2 rounded-md text-sm font-medium">Editar Notas</a>
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
            <h1 class="text-2xl font-bold mb-6">Editar Notas Fiscais</h1>
            
            <!-- Filtros -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Buscar por NF</label>
                    <input type="text" id="filtro-nf" placeholder="Digite o número da NF" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Cliente</label>
                    <select id="filtro-cliente" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todos os clientes</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Envio</label>
                    <select id="filtro-envio" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todos os envios</option>
                    </select>
                </div>
            </div>

            <!-- Tabela de registros -->
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
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-corpo" class="bg-white divide-y divide-gray-200">
                    </tbody>
                </table>
            </div>

            <!-- Cards para mobile -->
            <div id="cards-container" class="md:hidden space-y-4">
            </div>

            <div id="mensagem-tabela" class="text-center py-8 text-gray-500"></div>
        </div>
    </main>

    <!-- Modal de Edição -->
    <div id="modal-edicao" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Registro</h3>
            <form id="form-edicao">
                <input type="hidden" id="edit-registro-id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Número da NF</label>
                    <input type="text" id="edit-numero-nf" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
                    <select id="edit-cliente" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="">Selecione um cliente</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sub Cliente</label>
                    <select id="edit-sub-cliente" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Nenhum</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Produto</label>
                    <select id="edit-tipo-produto" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="">Selecione um tipo</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantidade</label>
                    <input type="number" id="edit-quantidade" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fecharModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    let registrosOriginais = [];
    let dadosFormulario = {};

    document.addEventListener('DOMContentLoaded', () => {
        carregarDadosFormulario();
        carregarRegistros();
        
        // Event listeners para filtros
        document.getElementById('filtro-nf').addEventListener('input', filtrarRegistros);
        document.getElementById('filtro-cliente').addEventListener('change', filtrarRegistros);
        document.getElementById('filtro-envio').addEventListener('change', filtrarRegistros);
        
        // Event listener para o formulário de edição
        document.getElementById('form-edicao').addEventListener('submit', salvarEdicao);
        
        // Event listener para mudança de cliente no modal
        document.getElementById('edit-cliente').addEventListener('change', atualizarSubClientes);
    });

    async function carregarDadosFormulario() {
        try {
            const response = await fetch('api.php?acao=listar_dados_formulario');
            dadosFormulario = await response.json();
            
            // Preencher filtros
            const filtroCliente = document.getElementById('filtro-cliente');
            dadosFormulario.clientes.forEach(cliente => {
                const option = document.createElement('option');
                option.value = cliente.id;
                option.textContent = cliente.nome;
                filtroCliente.appendChild(option);
            });
            
            const filtroEnvio = document.getElementById('filtro-envio');
            dadosFormulario.envios.forEach(envio => {
                const option = document.createElement('option');
                option.value = envio.id;
                option.textContent = envio.nome;
                filtroEnvio.appendChild(option);
            });
            
            // Preencher selects do modal
            const editCliente = document.getElementById('edit-cliente');
            dadosFormulario.clientes.forEach(cliente => {
                const option = document.createElement('option');
                option.value = cliente.id;
                option.textContent = cliente.nome;
                editCliente.appendChild(option);
            });
            
            const editTipoProduto = document.getElementById('edit-tipo-produto');
            dadosFormulario.tipos_produto.forEach(tipo => {
                const option = document.createElement('option');
                option.value = tipo.id;
                option.textContent = tipo.nome;
                editTipoProduto.appendChild(option);
            });
            
        } catch (error) {
            console.error('Erro ao carregar dados do formulário:', error);
        }
    }

    async function carregarRegistros() {
        const mensagemTabela = document.getElementById('mensagem-tabela');
        mensagemTabela.textContent = 'Carregando dados...';
        
        try {
            const response = await fetch('api.php?acao=listar_registros_edicao');
            const data = await response.json();

            if (!response.ok) throw new Error(data.error);

            registrosOriginais = data;
            exibirRegistros(data);
            
        } catch (error) {
            mensagemTabela.textContent = `Erro ao carregar dados: ${error.message}`;
        }
    }

    function exibirRegistros(registros) {
        const tabelaCorpo = document.getElementById('tabela-corpo');
        const cardsContainer = document.getElementById('cards-container');
        const mensagemTabela = document.getElementById('mensagem-tabela');

        if (registros.length === 0) {
            mensagemTabela.textContent = 'Nenhum registro encontrado.';
            tabelaCorpo.innerHTML = '';
            cardsContainer.innerHTML = '';
            return;
        }
        
        mensagemTabela.textContent = '';
        tabelaCorpo.innerHTML = '';
        cardsContainer.innerHTML = '';

        registros.forEach(reg => {
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="abrirModalEdicao(${reg.registro_id})" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                        <button onclick="deletarRegistro(${reg.registro_id}, '${reg.numero_nf}')" class="text-red-600 hover:text-red-900">Deletar</button>
                    </td>
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
                    <div class="text-sm space-y-1 mb-3">
                        <p><strong class="text-gray-600">Envio:</strong> ${reg.envio_nome}</p>
                        <p><strong class="text-gray-600">Cliente:</strong> ${reg.cliente_nome}</p>
                        <p><strong class="text-gray-600">Sub-Cliente:</strong> ${reg.sub_cliente_nome || 'N/A'}</p>
                        <p><strong class="text-gray-600">Tipo:</strong> ${reg.tipo_produto_nome}</p>
                        <p><strong class="text-gray-600">Quantidade:</strong> ${reg.quantidade}</p>
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="abrirModalEdicao(${reg.registro_id})" class="flex-1 bg-indigo-600 text-white px-3 py-2 rounded text-sm hover:bg-indigo-700">Editar</button>
                        <button onclick="deletarRegistro(${reg.registro_id}, '${reg.numero_nf}')" class="flex-1 bg-red-600 text-white px-3 py-2 rounded text-sm hover:bg-red-700">Deletar</button>
                    </div>
                </div>
            `;
            cardsContainer.innerHTML += card;
        });
    }

    function filtrarRegistros() {
        const filtroNF = document.getElementById('filtro-nf').value.toLowerCase();
        const filtroCliente = document.getElementById('filtro-cliente').value;
        const filtroEnvio = document.getElementById('filtro-envio').value;

        const registrosFiltrados = registrosOriginais.filter(reg => {
            const matchNF = !filtroNF || reg.numero_nf.toLowerCase().includes(filtroNF);
            const matchCliente = !filtroCliente || reg.cliente_id == filtroCliente;
            const matchEnvio = !filtroEnvio || (reg.envio_ids && reg.envio_ids.split(',').includes(filtroEnvio));
            
            return matchNF && matchCliente && matchEnvio;
        });

        exibirRegistros(registrosFiltrados);
    }

    function abrirModalEdicao(registroId) {
        const registro = registrosOriginais.find(r => r.registro_id == registroId);
        if (!registro) return;

        document.getElementById('edit-registro-id').value = registroId;
        document.getElementById('edit-numero-nf').value = registro.numero_nf;
        document.getElementById('edit-cliente').value = registro.cliente_id;
        document.getElementById('edit-tipo-produto').value = registro.tipo_produto_id;
        document.getElementById('edit-quantidade').value = registro.quantidade;
        
        atualizarSubClientes();
        setTimeout(() => {
            document.getElementById('edit-sub-cliente').value = registro.sub_cliente_id || '';
        }, 100);

        document.getElementById('modal-edicao').classList.remove('hidden');
    }

    function atualizarSubClientes() {
        const clienteId = document.getElementById('edit-cliente').value;
        const subClienteSelect = document.getElementById('edit-sub-cliente');
        
        subClienteSelect.innerHTML = '<option value="">Nenhum</option>';
        
        if (clienteId) {
            const subClientes = dadosFormulario.sub_clientes.filter(sc => sc.cliente_id == clienteId);
            subClientes.forEach(subCliente => {
                const option = document.createElement('option');
                option.value = subCliente.id;
                option.textContent = subCliente.nome;
                subClienteSelect.appendChild(option);
            });
        }
    }

    async function salvarEdicao(event) {
        event.preventDefault();
        
        const dados = {
            registro_id: document.getElementById('edit-registro-id').value,
            numero_nf: document.getElementById('edit-numero-nf').value,
            cliente_id: document.getElementById('edit-cliente').value,
            sub_cliente_id: document.getElementById('edit-sub-cliente').value || null,
            tipo_produto_id: document.getElementById('edit-tipo-produto').value,
            quantidade: document.getElementById('edit-quantidade').value
        };

        try {
            const response = await fetch('api.php?acao=editar_registro', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });

            const result = await response.json();

            if (response.ok) {
                alert('Registro atualizado com sucesso!');
                fecharModal();
                carregarRegistros();
            } else {
                alert('Erro: ' + result.error);
            }
        } catch (error) {
            alert('Erro ao salvar: ' + error.message);
        }
    }

    async function deletarRegistro(registroId, numeroNF) {
        if (!confirm(`Tem certeza que deseja deletar a NF ${numeroNF}?`)) return;

        try {
            const response = await fetch('api.php?acao=deletar_registro', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ registro_id: registroId })
            });

            const result = await response.json();

            if (response.ok) {
                alert('Registro deletado com sucesso!');
                carregarRegistros();
            } else {
                alert('Erro: ' + result.error);
            }
        } catch (error) {
            alert('Erro ao deletar: ' + error.message);
        }
    }

    function fecharModal() {
        document.getElementById('modal-edicao').classList.add('hidden');
    }
    </script>
</body>
</html>
