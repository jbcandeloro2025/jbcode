<?php require_once 'auth.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Registro - Gestão de NF</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        #btn-submit.loading::after {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 8px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
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
                            <a href="index.php" class="bg-indigo-100 text-indigo-700 px-3 py-2 rounded-md text-sm font-medium">Novo Registro</a>
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
                    <a href="#" id="logout-link" class="bg-red-500 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-red-600">Sair</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="p-4 sm:p-6 md:p-8">
        <div class="max-w-xl mx-auto bg-white p-6 sm:p-8 rounded-xl shadow-lg">
            <h1 class="text-2xl font-bold mb-2">Novo Registro</h1>
            <p class="text-sm text-gray-500 mb-6 border-b pb-4">Selecione todos os campos e uma cor disponível para o envio escolhido.</p>
            <form id="form-registro" class="space-y-4">
                <div>
                    <label for="numero_nf" class="block text-sm font-medium text-gray-700">Número da NF</label>
                    <input type="text" id="numero_nf" name="numero_nf" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="envio_id" class="block text-sm font-medium text-gray-700">Associar ao Envio</label>
                    <select id="envio_id" name="envio_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Carregando...</option>
                    </select>
                </div>
                <div>
                    <label for="cliente_id" class="block text-sm font-medium text-gray-700">Cliente</label>
                    <select id="cliente_id" name="cliente_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Carregando...</option>
                    </select>
                </div>
                <div>
                    <label for="sub_cliente_id" class="block text-sm font-medium text-gray-700">Sub-Cliente (Opcional)</label>
                    <select id="sub_cliente_id" name="sub_cliente_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" disabled>
                        <option value="">Selecione um cliente primeiro</option>
                    </select>
                </div>
                <div>
                    <label for="tipo_produto_id" class="block text-sm font-medium text-gray-700">Tipo de Produto</label>
                    <select id="tipo_produto_id" name="tipo_produto_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Carregando...</option>
                    </select>
                </div>
                <div>
                    <label for="combinacao_cor_id" class="block text-sm font-medium text-gray-700">Cor da Etiqueta</label>
                    <select id="combinacao_cor_id" name="combinacao_cor_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">Selecione um envio primeiro</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" required class="mt-1 block w-full pl-3 pr-10 py-2 border-gray-300 rounded-md">
                        <option value="" disabled selected>Selecione o Status</option>
                        <option value="Entrada">Entrada</option>
                        <option value="Preparo">Preparo</option>
                        <option value="Transporte">Transporte</option>
                    </select>
                </div>
                <div>
                    <label for="quantidade" class="block text-sm font-medium text-gray-700">Quantidade</label>
                    <input type="number" id="quantidade" name="quantidade" required class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" id="btn-submit" class="w-full flex justify-center items-center bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                    Adicionar Registro
                </button>
                <div id="mensagem-status" class="mt-4 text-sm text-center font-medium h-4"></div>
            </form>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form-registro');
            const selectCliente = document.getElementById('cliente_id');
            const selectSubCliente = document.getElementById('sub_cliente_id');
            const selectEnvio = document.getElementById('envio_id');
            const selectTipoProduto = document.getElementById('tipo_produto_id');
            const selectCor = document.getElementById('combinacao_cor_id');
            const selectStatus = document.getElementById('status');
            const btnSubmit = document.getElementById('btn-submit');
            const mensagemStatus = document.getElementById('mensagem-status');
            const logoutLink = document.getElementById('logout-link');

            let todosSubClientes = [];
            let todasCores = [];

            function popularSelect(selectElement, items, placeholder) {
                selectElement.innerHTML = `<option value="">${placeholder}</option>`;
                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.nome;
                    if (item.cliente_id) option.dataset.clienteId = item.cliente_id;
                    selectElement.appendChild(option);
                });
            }

            async function carregarDadosFormulario() {
                try {
                    const response = await fetch('api.php?acao=listar_cadastros');
                    if (!response.ok) throw new Error('Falha ao carregar dados do servidor.');
                    const data = await response.json();
                    if (data.error) throw new Error(data.error);

                    popularSelect(selectCliente, data.clientes || [], 'Selecione um Cliente');
                    popularSelect(selectEnvio, data.envios || [], 'Selecione um Envio');
                    popularSelect(selectTipoProduto, data.tipos_produto || [], 'Selecione um Tipo de Produto');
                    todasCores = data.combinacoes_cores || [];
                    todosSubClientes = data.sub_clientes || [];
                    selectSubCliente.innerHTML = '<option value="">Selecione um cliente primeiro</option>';
                    selectSubCliente.disabled = true;
                    selectCor.innerHTML = '<option value="">Selecione um envio primeiro</option>';
                    selectCor.disabled = true;
                } catch (error) {
                    mensagemStatus.textContent = 'Erro ao carregar dados. Tente recarregar a página.';
                    mensagemStatus.className = 'text-red-600';
                }
            }

            async function atualizarCoresDisponiveis() {
                const envioId = selectEnvio.value;
                if (!envioId) {
                    selectCor.innerHTML = '<option value="">Selecione um envio primeiro</option>';
                    selectCor.disabled = true;
                    return;
                }
                try {
                    const resp = await fetch('api.php?acao=registros_envio_cores&envio_id=' + envioId);
                    const usadas = await resp.json();
                    let usadasSet = new Set(Array.isArray(usadas) ? usadas : []);
                    selectCor.innerHTML = `<option value="">Selecione uma cor</option>`;
                    todasCores.forEach(cor => {
                        const option = document.createElement('option');
                        option.value = cor.id;
                        option.textContent = `${cor.nome} (${cor.hex_primario}${cor.hex_secundario ? ' / ' + cor.hex_secundario : ''})`;
                        option.style.background = cor.hex_primario;
                        if (usadasSet.has(String(cor.id)) || usadasSet.has(Number(cor.id))) {
                            option.disabled = true;
                            option.textContent += ' (em uso)';
                        }
                        selectCor.appendChild(option);
                    });
                    selectCor.disabled = false;
                } catch (e) {
                    selectCor.innerHTML = '<option value="">Erro ao carregar cores</option>';
                    selectCor.disabled = true;
                }
            }

            selectEnvio.addEventListener('change', atualizarCoresDisponiveis);

            selectCliente.addEventListener('change', function() {
                const clienteId = this.value;
                selectSubCliente.innerHTML = '<option value="">Selecione um Sub-Cliente (Opcional)</option>';
                if (clienteId) {
                    const subClientesFiltrados = todosSubClientes.filter(sub => sub.cliente_id == clienteId);
                    if (subClientesFiltrados.length > 0) {
                        subClientesFiltrados.forEach(sub => {
                            const option = document.createElement('option');
                            option.value = sub.id;
                            option.textContent = sub.nome;
                            selectSubCliente.appendChild(option);
                        });
                        selectSubCliente.disabled = false;
                    } else {
                        selectSubCliente.innerHTML = '<option value="">Nenhum Sub-Cliente encontrado</option>';
                        selectSubCliente.disabled = true;
                    }
                } else {
                    selectSubCliente.disabled = true;
                    selectSubCliente.innerHTML = '<option value="">Selecione um cliente primeiro</option>';
                }
            });

            form.addEventListener('submit', async function(event) {
                event.preventDefault();
                btnSubmit.disabled = true;
                btnSubmit.classList.add('loading');
                mensagemStatus.textContent = '';

                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());

                const statusValidos = ['Entrada', 'Preparo', 'Transporte'];
                if (!statusValidos.includes(data.status)) {
                    mensagemStatus.textContent = 'Por favor, selecione um status válido.';
                    mensagemStatus.className = 'text-red-600';
                    btnSubmit.disabled = false;
                    btnSubmit.classList.remove('loading');
                    return;
                }

                try {
                    const response = await fetch('api.php?acao=adicionar_registro', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        mensagemStatus.textContent = result.success;
                        mensagemStatus.className = 'text-green-600';
                        form.reset();
                        selectSubCliente.innerHTML = '<option value="">Selecione um cliente primeiro</option>';
                        selectSubCliente.disabled = true;
                        selectCor.innerHTML = '<option value="">Selecione um envio primeiro</option>';
                        selectCor.disabled = true;
                        document.getElementById('numero_nf').focus();
                    } else {
                        throw new Error(result.error || 'Ocorreu um erro desconhecido.');
                    }
                } catch (error) {
                    mensagemStatus.textContent = error.message;
                    mensagemStatus.className = 'text-red-600';
                } finally {
                    btnSubmit.disabled = false;
                    btnSubmit.classList.remove('loading');
                }
            });

            logoutLink.addEventListener('click', async (e) => {
                e.preventDefault();
                try {
                    await fetch('api.php?acao=logout', { method: 'POST' });
                } catch (error) {}
                finally {
                    window.location.href = 'login.php';
                }
            });

            carregarDadosFormulario();
        });
    </script>
</body>
</html>