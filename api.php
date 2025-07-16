<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

@require 'conexao.php';

$tabelasPermitidas = [
    'clientes',
    'sub_clientes',
    'tipos_produto',
    'envios',
    'combinacoes_cores',
    'registros',
    'envio_registros'
];

if (!isset($pdo)) {
    http_response_code(500);
    echo json_encode(['error' => 'Falha crítica na conexão com o banco de dados.']);
    exit;
}

function registrarLog($pdo, $acao, $detalhes = '') {
    try {
        $usuario_id = $_SESSION['usuario_id'] ?? null;
        $stmt = $pdo->prepare("INSERT INTO logs_sistema (usuario_id, acao, detalhes) VALUES (?, ?, ?)");
        $stmt->execute([$usuario_id, $acao, $detalhes]);
    } catch (PDOException $e) {
        // Falha silenciosa
    }
}

$acao = $_GET['acao'] ?? $_GET['action'] ?? null;

$acoesPublicas = [
    "login", "registrar_usuario", "reset_admin_password",
    "listar_dados_formulario", "listar_registros_tabela",
    "listar_cadastros", "listar_registros_edicao",
    "registros_envio_cores"
];
$acoesAdmin = [
    'listar_usuarios', 'aprovar_usuario', 'listar_logs', 'adicionar_item', 'deletar_item',
    'editar_registro', 'deletar_registro', 'listar_usuarios_completo', 'criar_usuario_admin',
    'obter_usuario', 'atualizar_usuario', 'deletar_usuario', 'listar_paginas_sistema',
    'obter_usuario_permissoes', 'salvar_permissoes_usuario'
];

if (!in_array($acao, $acoesPublicas)) {
    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Acesso não autorizado. Por favor, faça login.']);
        exit;
    }
    if (in_array($acao, $acoesAdmin) && (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'admin')) {
        http_response_code(403);
        echo json_encode(['error' => 'Acesso não autorizado. Permissões de administrador necessárias.']);
        exit;
    }
}

switch ($acao) {
    case 'login': login($pdo); break;
    case 'logout': logout($pdo); break;
    case 'registrar_usuario': registrarUsuario($pdo); break;
    case 'listar_usuarios': listarUsuarios($pdo); break;
    case 'listar_usuarios_completo': listarUsuariosCompleto($pdo); break;
    case 'criar_usuario_admin': criarUsuarioAdmin($pdo); break;
    case 'obter_usuario': obterUsuario($pdo); break;
    case 'atualizar_usuario': atualizarUsuario($pdo); break;
    case 'deletar_usuario': deletarUsuario($pdo); break;
    case 'aprovar_usuario': aprovarUsuario($pdo); break;
    case 'listar_paginas_sistema': listarPaginasSistema($pdo); break;
    case 'obter_usuario_permissoes': obterUsuarioPermissoes($pdo); break;
    case 'salvar_permissoes_usuario': salvarPermissoesUsuario($pdo); break;
    case 'listar_logs': listarLogs($pdo); break;
    case 'reset_admin_password': resetAdminPassword($pdo); break;
    case 'listar_dados_formulario': listarDadosFormulario($pdo); break;
    case 'listar_registros_tabela': listarRegistrosTabela($pdo); break;
    case 'listar_registros_edicao': listarRegistrosEdicao($pdo); break;
    case 'adicionar_registro': adicionarRegistro($pdo); break;
    case 'editar_registro': editarRegistro($pdo); break;
    case 'deletar_registro': deletarRegistro($pdo); break;
    case 'listar_cadastros': listarCadastros($pdo); break;
    case 'adicionar_item': adicionarItem($pdo); break;
    case 'deletar_item': deletarItem($pdo); break;
    case 'registros_envio_cores': registrosEnvioCores($pdo); break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Ação inválida.']);
}

// NOVA FUNÇÃO: retorna IDs de cores já usadas num envio
function registrosEnvioCores($pdo) {
    $envio_id = $_GET['envio_id'] ?? null;
    if (!$envio_id) {
        echo json_encode([]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT r.combinacao_cor_id FROM envio_registros er JOIN registros r ON er.registro_id = r.id WHERE er.envio_id = ?");
    $stmt->execute([$envio_id]);
    $ids = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'combinacao_cor_id');
    echo json_encode($ids);
    exit;
}

// ALTERADA: agora exige cor E STATUS e bloqueia cor já em uso
function adicionarRegistro($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    // Ajuste: status agora é obrigatório!
    if (
        empty($data['numero_nf']) || empty($data['envio_id']) ||
        empty($data['cliente_id']) || empty($data['tipo_produto_id']) ||
        empty($data['quantidade']) || empty($data['combinacao_cor_id']) ||
        empty($data['status'])
    ) {
        http_response_code(400);
        echo json_encode(['error' => 'Todos os campos obrigatórios devem ser preenchidos.']);
        return;
    }

    // Valida status permitido
    $statusPermitidos = ['Entrada', 'Preparo', 'Transporte'];
    if (!in_array($data['status'], $statusPermitidos)) {
        http_response_code(400);
        echo json_encode(['error' => 'Status inválido.']);
        return;
    }

    $pdo->beginTransaction();

    try {
        // Verifica se já existe registro com esse número de NF
        $stmt = $pdo->prepare("SELECT id FROM registros WHERE numero_nf = ?");
        $stmt->execute([$data['numero_nf']]);
        $registroExistente = $stmt->fetch();
        $registroId = null;

        if ($registroExistente) {
            $registroId = $registroExistente['id'];
        } else {
            // Cria novo registro com cor escolhida e status enviado
            $stmt = $pdo->prepare("
                INSERT INTO registros (numero_nf, cliente_id, sub_cliente_id, tipo_produto_id, quantidade, combinacao_cor_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['numero_nf'],
                $data['cliente_id'],
                empty($data['sub_cliente_id']) ? null : $data['sub_cliente_id'],
                $data['tipo_produto_id'],
                $data['quantidade'],
                $data['combinacao_cor_id'],
                $data['status']
            ]);
            $registroId = $pdo->lastInsertId();
        }

        // Verifica se a cor já está em uso nesse envio
        $stmt = $pdo->prepare("
            SELECT r.id 
            FROM envio_registros er
            JOIN registros r ON er.registro_id = r.id
            WHERE er.envio_id = ? AND r.combinacao_cor_id = ?
        ");
        $stmt->execute([$data['envio_id'], $data['combinacao_cor_id']]);
        if ($stmt->fetch()) {
            throw new Exception("Esta cor já está em uso neste envio. Selecione outra.");
        }

        // Verifica se já está associado a esse envio
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM envio_registros WHERE envio_id = ? AND registro_id = ?");
        $stmt->execute([$data['envio_id'], $registroId]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Esta NF já foi adicionada a este envio.");
        }

        // Associa registro ao envio
        $stmt = $pdo->prepare("INSERT INTO envio_registros (envio_id, registro_id) VALUES (?, ?)");
        $stmt->execute([$data['envio_id'], $registroId]);
        
        registrarLog($pdo, 'ADICIONAR_REGISTRO', "Adicionou a NF " . $data['numero_nf'] . " ao Envio ID " . $data['envio_id']);
        $pdo->commit();
        echo json_encode(['success' => 'Registro adicionado com sucesso!']);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(409);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function resetAdminPassword($pdo) {
    if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_nivel'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Acesso negado. Somente o administrador pode redefinir a senha.']);
        return;
    }
    try {
        $novaSenhaHash = password_hash('admin', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE nome = 'admin'");
        $stmt->execute([$novaSenhaHash]);
        if ($stmt->rowCount() > 0) {
            registrarLog($pdo, 'RESET_SENHA_ADMIN', 'A senha do usuário "admin" foi redefinida para o padrão.');
            echo json_encode(['success' => 'Senha do usuário "admin" redefinida para "admin" com sucesso.']);
        } else {
            throw new Exception('Usuário "admin" não encontrado.');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao redefinir a senha: ' . $e->getMessage()]);
    }
}

function login($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['nome']) || empty($data['senha'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Nome de usuário e senha são obrigatórios.']);
        return;
    }
    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nome = ?");
        $stmt->execute([$data['nome']]);
        $usuario = $stmt->fetch();
        if ($usuario && password_verify($data['senha'], $usuario['senha'])) {
            if ($usuario['status'] === 'aprovado') {
                session_regenerate_id();
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_nivel'] = $usuario['nivel'];
                registrarLog($pdo, 'LOGIN_SUCESSO', 'Usuário ' . $usuario['nome'] . ' logou no sistema.');
                echo json_encode(['success' => 'Login bem-sucedido.']);
            } else {
                registrarLog($pdo, 'LOGIN_FALHA', 'Tentativa de login para conta pendente: ' . $data['nome']);
                http_response_code(403);
                echo json_encode(['error' => 'Sua conta está aguardando aprovação.']);
            }
        } else {
            registrarLog($pdo, 'LOGIN_FALHA', 'Tentativa de login com credenciais inválidas para: ' . $data['nome']);
            http_response_code(401);
            echo json_encode(['error' => 'Usuário ou senha inválidos.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro no servidor.']);
    }
}

function logout($pdo) {
    if (isset($_SESSION["usuario_id"])) {
        $usuario_nome = $_SESSION['usuario_nome'] ?? 'Desconhecido';
        registrarLog($pdo, 'LOGOUT', 'Usuário ' . $usuario_nome . ' saiu do sistema.');
        session_unset();
        session_destroy();
    }
    echo json_encode(["success" => "Logout realizado com sucesso."]);
    exit;
}

function registrarUsuario($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['nome']) || empty($data['email']) || empty($data['senha'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Todos os campos são obrigatórios.']);
        return;
    }
    $senhaHash = password_hash($data['senha'], PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel, status) VALUES (?, ?, ?, 'usuario', 'pendente')");
        $stmt->execute([$data['nome'], $data['email'], $senhaHash]);
        registrarLog($pdo, 'NOVO_CADASTRO', 'Usuário ' . $data['nome'] . ' se cadastrou e aguarda aprovação.');
        echo json_encode(['success' => 'Cadastro realizado! Aguarde a aprovação.']);
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            http_response_code(409);
            echo json_encode(['error' => 'Este nome de usuário já está em uso.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao realizar o cadastro.']);
        }
    }
}

function listarUsuarios($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, nome, email, nivel, status FROM usuarios ORDER BY data_cadastro DESC");
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao listar usuários.']);
    }
}

function aprovarUsuario($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do usuário não fornecido.']);
        return;
    }
    try {
        $stmt = $pdo->prepare("UPDATE usuarios SET status = 'aprovado' WHERE id = ?");
        $stmt->execute([$id]);
        registrarLog($pdo, 'APROVACAO_USUARIO', 'Usuário com ID ' . $id . ' foi aprovado.');
        echo json_encode(['success' => 'Usuário aprovado com sucesso.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao aprovar usuário.']);
    }
}

function listarLogs($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                l.acao, l.detalhes, 
                DATE_FORMAT(l.timestamp, '%d/%m/%Y %H:%i:%s') as timestamp_formatado,
                u.nome as usuario_nome
            FROM logs_sistema l
            LEFT JOIN usuarios u ON l.usuario_id = u.id
            ORDER BY l.id DESC
            LIMIT 200
        ");
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar logs.']);
    }
}

function listarCadastros($pdo) {
    try {
        $output = [];
        $output['clientes'] = $pdo->query('SELECT id, nome FROM clientes ORDER BY nome')->fetchAll();
        $output['sub_clientes'] = $pdo->query('SELECT id, nome, cliente_id FROM sub_clientes ORDER BY nome')->fetchAll();
        $output['tipos_produto'] = $pdo->query('SELECT id, nome FROM tipos_produto ORDER BY nome')->fetchAll();
        $output['envios'] = $pdo->query('SELECT id, nome FROM envios ORDER BY nome')->fetchAll();
        $output['combinacoes_cores'] = $pdo
            ->query('
                SELECT 
                    id,
                    nome,
                    hex_primario,
                    hex_secundario
            FROM combinacoes_cores
            ORDER BY nome
        ')
        ->fetchAll();
        echo json_encode($output);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar dados de cadastros: ' . $e->getMessage()]);
    }
}

function adicionarItem($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $tabela = $data['tabela'] ?? '';
    $nome = trim($data['nome'] ?? '');
    $tabelasPermitidas = ['clientes', 'sub_clientes', 'tipos_produto', 'envios', 'combinacoes_cores'];
    if (!in_array($tabela, $tabelasPermitidas) || empty($nome)) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados inválidos.']);
        return;
    }
    try {
        if ($tabela === 'sub_clientes') {
            $cliente_id = $data['cliente_id'] ?? null;
            if (empty($cliente_id)) throw new Exception("É necessário selecionar um cliente pai.");
            $stmt = $pdo->prepare("INSERT INTO sub_clientes (nome, cliente_id) VALUES (?, ?)");
            $stmt->execute([$nome, $cliente_id]);
        } elseif ($tabela === 'combinacoes_cores') {
            $hex_primario = $data['hex_primario'] ?? null;
            $hex_secundario = $data['hex_secundario'] ?? null;
            if (empty($hex_primario)) throw new Exception("A cor primária é obrigatória.");
            $stmt = $pdo->prepare("INSERT INTO combinacoes_cores (nome, hex_primario, hex_secundario) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $hex_primario, $hex_secundario]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO $tabela (nome) VALUES (?)");
            $stmt->execute([$nome]);
        }
        registrarLog($pdo, 'ADICIONAR_ITEM_CADASTRO', "Adicionou o item '$nome' na tabela '$tabela'.");
        echo json_encode(['success' => 'Item adicionado com sucesso.']);
    } catch (PDOException $e) {
        http_response_code(409);
        echo json_encode(['error' => 'Não foi possível adicionar. O item já existe?']);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function deletarItem($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $tabela = $data['tabela'] ?? '';
    $id = $data['id'] ?? null;
    $tabelasPermitidas = ['clientes', 'sub_clientes', 'tipos_produto', 'envios', 'combinacoes_cores'];
    if (!in_array($tabela, $tabelasPermitidas) || empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados inválidos para exclusão.']);
        return;
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM $tabela WHERE id = ?");
        $stmt->execute([$id]);
        registrarLog($pdo, 'DELETAR_ITEM_CADASTRO', "Deletou o item com ID $id da tabela '$tabela'.");
        echo json_encode(['success' => 'Item deletado com sucesso.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Não foi possível deletar. O item pode estar em uso em algum registro.']);
    }
}

function listarDadosFormulario($pdo) {
    try {
        $output = [];
        $output['clientes'] = $pdo->query('SELECT id, nome FROM clientes ORDER BY nome')->fetchAll();
        $output['sub_clientes'] = $pdo->query('SELECT id, nome, cliente_id FROM sub_clientes ORDER BY nome')->fetchAll();
        $output['tipos_produto'] = $pdo->query('SELECT id, nome FROM tipos_produto ORDER BY nome')->fetchAll();
        $output['envios'] = $pdo->query('SELECT id, nome FROM envios ORDER BY nome')->fetchAll();
        echo json_encode($output);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro de SQL: ' . $e->getMessage()]);
    }
}

function listarRegistrosTabela($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT
                DATE_FORMAT(r.data_criacao, '%d/%m/%y') as data_formatada,
                cc.nome as combinacao_nome,
                cc.hex_primario,
                cc.hex_secundario,
                r.numero_nf,
                e.nome as envio_nome,
                c.nome as cliente_nome,
                sc.nome as sub_cliente_nome,
                tp.nome as tipo_produto_nome,
                r.quantidade,
                r.status
            FROM envio_registros er
            JOIN registros r ON er.registro_id = r.id
            JOIN envios e ON er.envio_id = e.id
            JOIN clientes c ON r.cliente_id = c.id
            JOIN tipos_produto tp ON r.tipo_produto_id = tp.id
            JOIN combinacoes_cores cc ON r.combinacao_cor_id = cc.id
            LEFT JOIN sub_clientes sc ON r.sub_cliente_id = sc.id
            ORDER BY er.data_associacao DESC
        ");
        $registros = $stmt->fetchAll();
        echo json_encode($registros);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro de SQL: ' . $e->getMessage()]);
    }
}

function listarRegistrosEdicao($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT
                r.id as registro_id,
                DATE_FORMAT(r.data_criacao, '%d/%m/%y') as data_formatada,
                cc.nome as combinacao_nome,
                cc.hex_primario,
                cc.hex_secundario,
                r.numero_nf,
                r.cliente_id,
                r.sub_cliente_id,
                r.tipo_produto_id,
                r.quantidade,
                c.nome as cliente_nome,
                sc.nome as sub_cliente_nome,
                tp.nome as tipo_produto_nome,
                r.status,
                GROUP_CONCAT(DISTINCT e.id) as envio_ids,
                GROUP_CONCAT(DISTINCT e.nome SEPARATOR ', ') as envio_nome
            FROM registros r
            JOIN clientes c ON r.cliente_id = c.id
            JOIN tipos_produto tp ON r.tipo_produto_id = tp.id
            JOIN combinacoes_cores cc ON r.combinacao_cor_id = cc.id
            LEFT JOIN sub_clientes sc ON r.sub_cliente_id = sc.id
            LEFT JOIN envio_registros er ON r.id = er.registro_id
            LEFT JOIN envios e ON er.envio_id = e.id
            GROUP BY r.id
            ORDER BY r.data_criacao DESC
        ");
        $registros = $stmt->fetchAll();
        echo json_encode($registros);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro de SQL: ' . $e->getMessage()]);
    }
}

function editarRegistro($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $registro_id = $data['registro_id'] ?? null;
    $numero_nf = trim($data['numero_nf'] ?? '');
    $cliente_id = $data['cliente_id'] ?? null;
    $sub_cliente_id = $data['sub_cliente_id'] ?? null;
    $tipo_produto_id = $data['tipo_produto_id'] ?? null;
    $quantidade = $data['quantidade'] ?? null;
    $status = $data['status'] ?? null;

    if (empty($registro_id) || empty($numero_nf) || empty($cliente_id) || empty($tipo_produto_id) || empty($quantidade) || empty($status)) {
        http_response_code(400);
        echo json_encode(['error' => 'Todos os campos obrigatórios devem ser preenchidos.']);
        return;
    }
    $statusPermitidos = ['Entrada', 'Preparo', 'Transporte'];
    if (!in_array($status, $statusPermitidos)) {
        http_response_code(400);
        echo json_encode(['error' => 'Status inválido.']);
        return;
    }
    try {
        $stmt = $pdo->prepare("SELECT id FROM registros WHERE numero_nf = ? AND id != ?");
        $stmt->execute([$numero_nf, $registro_id]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Este número de NF já está sendo usado em outro registro.']);
            return;
        }
        $stmt = $pdo->prepare("
            UPDATE registros 
            SET numero_nf = ?, cliente_id = ?, sub_cliente_id = ?, tipo_produto_id = ?, quantidade = ?, status = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $numero_nf,
            $cliente_id,
            $sub_cliente_id ?: null,
            $tipo_produto_id,
            $quantidade,
            $status,
            $registro_id
        ]);
        registrarLog($pdo, 'EDITAR_REGISTRO', "Editou o registro ID $registro_id (NF: $numero_nf)");
        echo json_encode(['success' => 'Registro atualizado com sucesso.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao atualizar registro: ' . $e->getMessage()]);
    }
}

function deletarRegistro($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $registro_id = $data['registro_id'] ?? null;
    if (empty($registro_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do registro não fornecido.']);
        return;
    }
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT numero_nf FROM registros WHERE id = ?");
        $stmt->execute([$registro_id]);
        $registro = $stmt->fetch();
        if (!$registro) {
            http_response_code(404);
            echo json_encode(['error' => 'Registro não encontrado.']);
            return;
        }
        $stmt = $pdo->prepare("DELETE FROM envio_registros WHERE registro_id = ?");
        $stmt->execute([$registro_id]);
        $stmt = $pdo->prepare("DELETE FROM registros WHERE id = ?");
        $stmt->execute([$registro_id]);
        registrarLog($pdo, 'DELETAR_REGISTRO', "Deletou o registro ID $registro_id (NF: {$registro['numero_nf']})");
        $pdo->commit();
        echo json_encode(['success' => 'Registro deletado com sucesso.']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao deletar registro: ' . $e->getMessage()]);
    }
}

function listarUsuariosCompleto($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, nome, email, nivel, status FROM usuarios ORDER BY data_cadastro DESC");
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao listar usuários.']);
    }
}

function criarUsuarioAdmin($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (empty($data['nome']) || empty($data['email']) || empty($data['senha']) || empty($data['nivel'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Todos os campos são obrigatórios.']);
        return;
    }
    $senhaHash = password_hash($data['senha'], PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel, status) VALUES (?, ?, ?, ?, 'aprovado')");
        $stmt->execute([$data['nome'], $data['email'], $senhaHash, $data['nivel']]);
        $usuarioId = $pdo->lastInsertId();
        if ($data['nivel'] === 'admin') {
            $stmt = $pdo->prepare("
                INSERT INTO usuario_permissoes (usuario_id, pagina_id)
                SELECT ?, id FROM paginas_sistema
            ");
            $stmt->execute([$usuarioId]);
        }
        registrarLog($pdo, 'CRIAR_USUARIO', 'Usuário ' . $data['nome'] . ' criado pelo administrador.');
        echo json_encode(['success' => 'Usuário criado com sucesso!']);
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            http_response_code(409);
            echo json_encode(['error' => 'Este nome de usuário já está em uso.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao criar o usuário.']);
        }
    }
}

function obterUsuario($pdo) {
    $id = $_GET['id'] ?? null;
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do usuário não fornecido.']);
        return;
    }
    try {
        $stmt = $pdo->prepare("SELECT id, nome, email, nivel, status FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        if ($usuario) {
            echo json_encode($usuario);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Usuário não encontrado.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar usuário.']);
    }
}

function atualizarUsuario($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    $nome = trim($data['nome'] ?? '');
    $email = trim($data['email'] ?? '');
    $nivel = $data['nivel'] ?? null;
    $senha = $data['senha'] ?? null;
    if (empty($id) || empty($nome) || empty($email) || empty($nivel)) {
        http_response_code(400);
        echo json_encode(['error' => 'Todos os campos obrigatórios devem ser preenchidos.']);
        return;
    }
    try {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE nome = ? AND id != ?");
        $stmt->execute([$nome, $id]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Este nome de usuário já está sendo usado por outro usuário.']);
            return;
        }
        if ($senha) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, nivel = ?, senha = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $nivel, $senhaHash, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, nivel = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $nivel, $id]);
        }
        if ($nivel === 'admin') {
            $stmt = $pdo->prepare("DELETE FROM usuario_permissoes WHERE usuario_id = ?");
            $stmt->execute([$id]);
            $stmt = $pdo->prepare("
                INSERT INTO usuario_permissoes (usuario_id, pagina_id)
                SELECT ?, id FROM paginas_sistema
            ");
            $stmt->execute([$id]);
        }
        registrarLog($pdo, 'ATUALIZAR_USUARIO', "Usuário ID $id ($nome) foi atualizado.");
        echo json_encode(['success' => 'Usuário atualizado com sucesso.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao atualizar usuário: ' . $e->getMessage()]);
    }
}

function deletarUsuario($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do usuário não fornecido.']);
        return;
    }
    try {
        $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuário não encontrado.']);
            return;
        }
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        registrarLog($pdo, 'DELETAR_USUARIO', "Usuário {$usuario['nome']} (ID: $id) foi deletado.");
        echo json_encode(['success' => 'Usuário deletado com sucesso.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao deletar usuário: ' . $e->getMessage()]);
    }
}

function listarPaginasSistema($pdo) {
    try {
        $stmt = $pdo->query("SELECT id, nome, descricao, arquivo FROM paginas_sistema ORDER BY nome");
        echo json_encode($stmt->fetchAll());
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao listar páginas do sistema.']);
    }
}

function obterUsuarioPermissoes($pdo) {
    $id = $_GET['id'] ?? null;
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do usuário não fornecido.']);
        return;
    }
    try {
        $stmt = $pdo->prepare("SELECT id, nome, email, nivel FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuário não encontrado.']);
            return;
        }
        $stmt = $pdo->prepare("
            SELECT up.pagina_id, ps.nome, ps.descricao 
            FROM usuario_permissoes up 
            JOIN paginas_sistema ps ON up.pagina_id = ps.id 
            WHERE up.usuario_id = ?
        ");
        $stmt->execute([$id]);
        $permissoes = $stmt->fetchAll();
        echo json_encode([
            'usuario' => $usuario,
            'permissoes' => $permissoes
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar permissões do usuário.']);
    }
}

function salvarPermissoesUsuario($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $usuarioId = $data['usuario_id'] ?? null;
    $paginas = $data['paginas'] ?? [];
    if (empty($usuarioId)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID do usuário não fornecido.']);
        return;
    }
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("DELETE FROM usuario_permissoes WHERE usuario_id = ?");
        $stmt->execute([$usuarioId]);
        if (!empty($paginas)) {
            $stmt = $pdo->prepare("INSERT INTO usuario_permissoes (usuario_id, pagina_id) VALUES (?, ?)");
            foreach ($paginas as $paginaId) {
                $stmt->execute([$usuarioId, $paginaId]);
            }
        }
        registrarLog($pdo, 'ATUALIZAR_PERMISSOES', "Permissões do usuário ID $usuarioId foram atualizadas.");
        $pdo->commit();
        echo json_encode(['success' => 'Permissões salvas com sucesso.']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao salvar permissões: ' . $e->getMessage()]);
    }
}

?>
