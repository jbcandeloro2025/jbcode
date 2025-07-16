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
    <title>Gerenciar Usuários - Gestão Logística</title>
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
                                <a href="gerenciar_usuarios.php" class="bg-indigo-100 text-indigo-700 px-3 py-2 rounded-md text-sm font-medium">Usuários</a>
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
        <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow-lg">
            <h1 class="text-2xl font-bold mb-6">Gerenciar Usuários</h1>
            <div id="status-geral" class="text-center font-medium mb-4 h-4"></div>
            
            <!-- Botão para adicionar novo usuário -->
            <div class="mb-6">
                <button onclick="abrirModalNovoUsuario()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 font-medium">
                    + Novo Usuário
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nível</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="lista-usuarios" class="bg-white divide-y divide-gray-200"></tbody>
                </table>
                <div id="status-lista" class="text-center py-4 text-gray-500"></div>
            </div>
        </div>
    </main>

    <!-- Modal para Novo Usuário -->
    <div id="modal-novo-usuario" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Novo Usuário</h3>
            <form id="form-novo-usuario">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome de Usuário</label>
                    <input type="text" id="novo-nome" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="novo-email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                    <input type="password" id="novo-senha" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nível</label>
                    <select id="novo-nivel" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="usuario">Usuário</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fecharModalNovoUsuario()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Criar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Editar Usuário -->
    <div id="modal-editar-usuario" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Editar Usuário</h3>
            <form id="form-editar-usuario">
                <input type="hidden" id="edit-usuario-id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nome de Usuário</label>
                    <input type="text" id="edit-nome" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="edit-email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nova Senha (deixe em branco para manter)</label>
                    <input type="password" id="edit-senha" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nível</label>
                    <select id="edit-nivel" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="usuario">Usuário</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="fecharModalEditarUsuario()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Gerenciar Permissões -->
    <div id="modal-permissoes" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Gerenciar Permissões</h3>
            <div id="permissoes-usuario-info" class="mb-4 p-3 bg-gray-100 rounded-md"></div>
            
            <div id="lista-permissoes" class="space-y-2 mb-4 max-h-60 overflow-y-auto">
                <!-- Permissões serão carregadas aqui -->
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="fecharModalPermissoes()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Fechar</button>
                <button type="button" onclick="salvarPermissoes()" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Salvar Permissões</button>
            </div>
        </div>
    </div>

    <script>
    let usuarioAtual = null;
    let paginasSistema = [];

    document.addEventListener('DOMContentLoaded', () => {
        carregarUsuarios();
        carregarPaginasSistema();
        
        // Event listeners para formulários
        document.getElementById('form-novo-usuario').addEventListener('submit', criarUsuario);
        document.getElementById('form-editar-usuario').addEventListener('submit', salvarEdicaoUsuario); // Alterado para salvarEdicaoUsuario
    });

    async function carregarPaginasSistema() {
        try {
            const response = await fetch('api.php?acao=listar_paginas_sistema');
            const data = await response.json();
            if (response.ok) {
                paginasSistema = data;
            }
        } catch (error) {
            console.error('Erro ao carregar páginas do sistema:', error);
        }
    }

    async function carregarUsuarios() {
        const listaUsuarios = document.getElementById('lista-usuarios');
        const statusLista = document.getElementById('status-lista');
        
        statusLista.textContent = 'Carregando...';
        try {
            const response = await fetch('api.php?acao=listar_usuarios_completo');
            const data = await response.json();
            if (!response.ok) throw new Error(data.error);
            
            listaUsuarios.innerHTML = '';
            if (data.length === 0) {
                statusLista.textContent = 'Nenhum usuário cadastrado.';
                return;
            }
            statusLista.textContent = '';
            
            data.forEach(user => {
                const nivelBadge = user.nivel === 'admin' 
                    ? `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Admin</span>`
                    : `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Usuário</span>`;
                
                let statusBadge;
                let acoes = '';

                if (user.status === 'aprovado') {
                    statusBadge = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Aprovado</span>`;
                    acoes = `
                        <button onclick="abrirModalEditarUsuario(${user.id})" class="text-xs bg-blue-500 text-white font-bold py-1 px-2 rounded hover:bg-blue-600 mr-1">Editar</button>
                        ${user.nivel === 'usuario' ? `<button onclick="gerenciarPermissoes(${user.id})" class="text-xs bg-purple-500 text-white font-bold py-1 px-2 rounded hover:bg-purple-600 mr-1">Permissões</button>` : ''}
                        <button onclick="deletarUsuario(${user.id}, '${user.nome}')" class="text-xs bg-red-500 text-white font-bold py-1 px-2 rounded hover:bg-red-600">Deletar</button>
                    `;
                } else {
                    statusBadge = `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendente</span>`;
                    acoes = `<button onclick="aprovarUsuario(${user.id})" class="text-xs bg-green-500 text-white font-bold py-1 px-2 rounded hover:bg-green-600">Aprovar</button>`;
                }

                listaUsuarios.innerHTML += `
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap">${user.nome}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${user.email}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${nivelBadge}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${statusBadge}</td>
                        <td class="px-4 py-2 whitespace-nowrap">${acoes}</td>
                    </tr>`;
            });
        } catch (error) {
            statusLista.textContent = `Erro: ${error.message}`;
        }
    }

    function abrirModalNovoUsuario() {
        document.getElementById('modal-novo-usuario').classList.remove('hidden');
    }

    function fecharModalNovoUsuario() {
        document.getElementById('modal-novo-usuario').classList.add('hidden');
        document.getElementById('form-novo-usuario').reset();
    }

    async function criarUsuario(event) {
        event.preventDefault();
        
        const dados = {
            nome: document.getElementById('novo-nome').value,
            email: document.getElementById('novo-email').value,
            senha: document.getElementById('novo-senha').value,
            nivel: document.getElementById('novo-nivel').value
        };

        try {
            const response = await fetch('api.php?acao=criar_usuario_admin', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });

            const result = await response.json();

            if (response.ok) {
                mostrarStatus(result.success, 'green');
                fecharModalNovoUsuario();
                carregarUsuarios();
            } else {
                alert('Erro: ' + result.error);
            }
        } catch (error) {
            alert('Erro ao criar usuário: ' + error.message);
        }
    }

    async function abrirModalEditarUsuario(userId) { // Renomeado para abrirModalEditarUsuario
        try {
            const response = await fetch(`api.php?acao=obter_usuario&id=${userId}`);
            const user = await response.json();
            
            if (response.ok) {
                document.getElementById('edit-usuario-id').value = user.id;
                document.getElementById('edit-nome').value = user.nome;
                document.getElementById('edit-email').value = user.email;
                document.getElementById('edit-nivel').value = user.nivel;
                document.getElementById('edit-senha').value = '';
                
                document.getElementById('modal-editar-usuario').classList.remove('hidden');
            } else {
                alert('Erro: ' + user.error);
            }
        } catch (error) {
            alert('Erro ao carregar usuário: ' + error.message);
        }
    }

    function fecharModalEditarUsuario() {
        document.getElementById('modal-editar-usuario').classList.add('hidden');
    }

    async function salvarEdicaoUsuario(event) { // Renomeado para salvarEdicaoUsuario
        event.preventDefault();
        
        const dados = {
            id: document.getElementById('edit-usuario-id').value,
            nome: document.getElementById('edit-nome').value,
            email: document.getElementById('edit-email').value,
            nivel: document.getElementById('edit-nivel').value
        };

        const senha = document.getElementById('edit-senha').value;
        if (senha) {
            dados.senha = senha;
        }

        try {
            const response = await fetch('api.php?acao=atualizar_usuario', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            });

            const result = await response.json();

            if (response.ok) {
                mostrarStatus(result.success, 'green');
                fecharModalEditarUsuario();
                carregarUsuarios();
            } else {
                alert('Erro: ' + result.error);
            }
        } catch (error) {
            alert('Erro ao atualizar usuário: ' + error.message);
        }
    }

    async function aprovarUsuario(userId) {
        try {
            const response = await fetch('api.php?acao=aprovar_usuario', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId })
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.error);
            
            mostrarStatus(result.success, 'green');
            carregarUsuarios();

        } catch (error) {
            mostrarStatus(`Erro: ${error.message}`, 'red');
        }
    }

    async function deletarUsuario(userId, nomeUsuario) {
        if (!confirm(`Tem certeza que deseja deletar o usuário "${nomeUsuario}"?`)) return;

        try {
            const response = await fetch('api.php?acao=deletar_usuario', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId })
            });

            const result = await response.json();

            if (response.ok) {
                mostrarStatus(result.success, 'green');
                carregarUsuarios();
            } else {
                alert('Erro: ' + result.error);
            }
        } catch (error) {
            alert('Erro ao deletar usuário: ' + error.message);
        }
    }

    async function gerenciarPermissoes(userId) {
        try {
            const response = await fetch(`api.php?acao=obter_usuario_permissoes&id=${userId}`);
            const data = await response.json();
            
            if (response.ok) {
                usuarioAtual = data.usuario;
                const permissoesUsuario = data.permissoes;
                
                document.getElementById('permissoes-usuario-info').innerHTML = `
                    <strong>Usuário:</strong> ${usuarioAtual.nome} (${usuarioAtual.email})
                `;
                
                const listaPermissoes = document.getElementById('lista-permissoes');
                listaPermissoes.innerHTML = '';
                
                paginasSistema.forEach(pagina => {
                    // Pular páginas exclusivas de admin
                    if (pagina.nome === 'Usuários' || pagina.nome === 'Logs') return;
                    
                    const temPermissao = permissoesUsuario.some(p => p.pagina_id == pagina.id);
                    
                    listaPermissoes.innerHTML += `
                        <div class="flex items-center justify-between p-2 border rounded">
                            <div>
                                <strong>${pagina.nome}</strong>
                                <p class="text-sm text-gray-600">${pagina.descricao}</p>
                            </div>
                            <input type="checkbox" data-pagina-id="${pagina.id}" ${temPermissao ? 'checked' : ''} class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                    `;
                });
                
                document.getElementById('modal-permissoes').classList.remove('hidden');
            } else {
                alert('Erro: ' + data.error);
            }
        } catch (error) {
            alert('Erro ao carregar permissões: ' + error.message);
        }
    }

    function fecharModalPermissoes() {
        document.getElementById('modal-permissoes').classList.add('hidden');
        usuarioAtual = null;
    }

    async function salvarPermissoes() {
        if (!usuarioAtual) return;

        const checkboxes = document.querySelectorAll('#lista-permissoes input[type="checkbox"]');
        const permissoes = [];
        
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                permissoes.push(parseInt(checkbox.dataset.paginaId));
            }
        });

        try {
            const response = await fetch('api.php?acao=salvar_permissoes_usuario', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    usuario_id: usuarioAtual.id,
                    paginas: permissoes
                })
            });

            const result = await response.json();

            if (response.ok) {
                mostrarStatus(result.success, 'green');
                fecharModalPermissoes();
            } else {
                alert('Erro: ' + result.error);
            }
        } catch (error) {
            alert('Erro ao salvar permissões: ' + error.message);
        }
    }

    function mostrarStatus(mensagem, cor) {
        const statusGeral = document.getElementById('status-geral');
        statusGeral.textContent = mensagem;
        statusGeral.className = `text-center font-medium mb-4 h-4 text-${cor}-600`;
        setTimeout(() => {
            statusGeral.textContent = '';
            statusGeral.className = 'text-center font-medium mb-4 h-4';
        }, 3000);
    }
    </script>
</body>
</html>


