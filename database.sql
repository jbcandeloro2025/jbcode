-- 1. Criar Banco de Dados
CREATE DATABASE IF NOT EXISTS `gerenciador_logistico` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `gerenciador_logistico`;

-- 2. Tabela de Usuários
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único do usuário',
    `nome` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nome de usuário (único)',
    `email` VARCHAR(100) NOT NULL COMMENT 'E-mail de contato',
    `senha` VARCHAR(255) NOT NULL COMMENT 'Senha criptografada',
    `nivel` ENUM('usuario','admin') NOT NULL DEFAULT 'usuario' COMMENT 'Permissão do usuário',
    `status` ENUM('pendente','aprovado') NOT NULL DEFAULT 'pendente' COMMENT 'Status de aprovação',
    `data_cadastro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    `data_atualizacao` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data da última alteração'
) ENGINE=InnoDB COMMENT='Tabela de usuários do sistema';

INSERT IGNORE INTO `usuarios` (`id`,`nome`,`email`,`senha`,`nivel`,`status`) VALUES
(1,'admin','admin@sistema.com','$2y$10$g0bLssSg8O2rrZgCRQo8a.7jJ/v1c.d4e.IqG.g/9jJ4kK.L4c.h','admin','aprovado');

-- 3. Logs do Sistema
CREATE TABLE IF NOT EXISTS `logs_sistema` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador do log',
    `usuario_id` INT COMMENT 'Usuário responsável',
    `acao` VARCHAR(255) NOT NULL COMMENT 'Ação realizada',
    `detalhes` TEXT COMMENT 'Detalhes do evento',
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data/hora do log',
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB COMMENT='Registro de todas as ações importantes do sistema';

-- 4. Páginas do Sistema
CREATE TABLE IF NOT EXISTS `paginas_sistema` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID da página',
    `nome` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nome da página',
    `descricao` VARCHAR(255) NOT NULL COMMENT 'Descrição da página',
    `arquivo` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Nome do arquivo PHP'
) ENGINE=InnoDB COMMENT='Lista de páginas do sistema para controle de permissões';

INSERT IGNORE INTO `paginas_sistema` (`nome`,`descricao`,`arquivo`) VALUES
('Dashboard','Página inicial do sistema','dashboard.php'),
('Novo Registro','Página para adicionar novos registros de NF','index.php'),
('Visualização','Página para visualizar todos os registros','visualizacao.php'),
('Editar Notas','Página para editar e deletar registros de NF','editar_notas.php'),
('Cadastros','Página para gerenciar cadastros básicos','cadastros.php'),
('Usuários','Página para gerenciar usuários do sistema (apenas admin)','gerenciar_usuarios.php'),
('Logs','Página para visualizar logs de atividades do sistema (apenas admin)','logs.php');

-- 5. Permissões de Usuário
CREATE TABLE IF NOT EXISTS `usuario_permissoes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL COMMENT 'Usuário relacionado',
    `pagina_id` INT NOT NULL COMMENT 'Página acessível',
    `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Quando a permissão foi criada',
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`pagina_id`) REFERENCES `paginas_sistema`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_usuario_pagina` (`usuario_id`,`pagina_id`)
) ENGINE=InnoDB COMMENT='Permissões de acesso de cada usuário às páginas do sistema';

INSERT IGNORE INTO `usuario_permissoes` (`usuario_id`,`pagina_id`)
SELECT u.id,p.id FROM `usuarios` u CROSS JOIN `paginas_sistema` p WHERE u.nivel='admin';

-- 6. Combinações de Cores
CREATE TABLE IF NOT EXISTS `combinacoes_cores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID da cor',
    `nome` VARCHAR(100) NOT NULL COMMENT 'Nome da combinação',
    `hex_primario` VARCHAR(7) NOT NULL COMMENT 'Cor primária em HEX',
    `hex_secundario` VARCHAR(7) COMMENT 'Cor secundária em HEX'
) ENGINE=InnoDB COMMENT='Combinações de cores para identificação visual';

INSERT IGNORE INTO `combinacoes_cores` (`nome`,`hex_primario`,`hex_secundario`) VALUES
('Azul Sólido','#3b82f6',NULL),
('Verde Sólido','#22c55e',NULL),
('Roxo Sólido','#8b5cf6',NULL);

-- 7. Clientes
CREATE TABLE IF NOT EXISTS `clientes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID do cliente',
    `nome` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Nome do cliente'
) ENGINE=InnoDB COMMENT='Clientes principais';

INSERT IGNORE INTO `clientes` (`nome`) VALUES
('Cliente A'),
('Cliente B');

-- 8. Sub-Clientes
CREATE TABLE IF NOT EXISTS `sub_clientes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID do sub-cliente',
    `nome` VARCHAR(255) NOT NULL COMMENT 'Nome do sub-cliente',
    `cliente_id` INT NOT NULL COMMENT 'Cliente principal',
    FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='Sub-clientes associados a um cliente principal';

INSERT IGNORE INTO `sub_clientes` (`nome`,`cliente_id`) VALUES
('Sub A1',1),
('Sub A2',1),
('Sub B1',2);

-- 9. Tipos de Produto
CREATE TABLE IF NOT EXISTS `tipos_produto` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID do tipo de produto',
    `nome` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Nome do tipo'
) ENGINE=InnoDB COMMENT='Tipos de produtos cadastrados';

INSERT IGNORE INTO `tipos_produto` (`nome`) VALUES
('Eletrônicos'),
('Vestuário'),
('Alimentos');

-- 10. Envios
CREATE TABLE IF NOT EXISTS `envios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID do envio',
    `nome` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Nome do envio',
    `data_cadastro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação',
    `data_atualizacao` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data da última alteração'
) ENGINE=InnoDB COMMENT='Registros de envios';

INSERT IGNORE INTO `envios` (`nome`) VALUES
('Envio 1'),
('Envio 2'),
('Envio 3');

-- 11. Registros (AJUSTADO)
CREATE TABLE IF NOT EXISTS `registros` (
    `id` INT AUTO_INCREMENT PRIMARY KEY COMMENT 'ID do registro',
    `numero_nf` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Número da nota fiscal',
    `data_emissao` DATE COMMENT 'Data de emissão da nota',
    `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação do registro',
    `data_atualizacao` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Data da última alteração do registro',
    `quantidade` INT NOT NULL DEFAULT 0 COMMENT 'Quantidade de itens',
    `combinacao_cor_id` INT DEFAULT NULL COMMENT 'Identificador da combinação de cor',
    `cliente_id` INT NOT NULL COMMENT 'Identificador do cliente',
    `sub_cliente_id` INT DEFAULT NULL COMMENT 'Identificador do sub-cliente',
    `tipo_produto_id` INT NOT NULL COMMENT 'Tipo do produto',
    `valor` DECIMAL(10,2) COMMENT 'Valor total da nota',
    `status` ENUM('Entrada','Preparo','Transporte') NOT NULL DEFAULT 'Entrada' COMMENT 'Status da nota',
    FOREIGN KEY (`combinacao_cor_id`) REFERENCES `combinacoes_cores`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`sub_cliente_id`) REFERENCES `sub_clientes`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`tipo_produto_id`) REFERENCES `tipos_produto`(`id`) ON DELETE CASCADE,
    INDEX idx_numero_nf (`numero_nf`),
    INDEX idx_data_criacao (`data_criacao`),
    INDEX idx_cliente (`cliente_id`),
    INDEX idx_tipo_produto (`tipo_produto_id`)
) ENGINE=InnoDB COMMENT='Registros de notas fiscais e informações associadas';

-- 12. Associação Envios-Registros
CREATE TABLE IF NOT EXISTS `envio_registros` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `envio_id` INT NOT NULL COMMENT 'Envio relacionado',
    `registro_id` INT NOT NULL COMMENT 'Registro de nota relacionado',
    `data_associacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data da associação',
    FOREIGN KEY (`envio_id`) REFERENCES `envios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`registro_id`) REFERENCES `registros`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_envio_registro` (`envio_id`,`registro_id`)
) ENGINE=InnoDB COMMENT='Associação entre envios e registros (notas fiscais)';
