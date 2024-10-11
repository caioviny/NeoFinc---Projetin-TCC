CREATE DATABASE finance;

USE finance;

-- Criação da tabela de usuários
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    remember_token VARCHAR(64), -- Coluna remember_token adicionada
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Criação da tabela para armazenar tentativas de login
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- Criação da tabela para armazenar códigos de redefinição de senha
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reset_code VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- Criação da tabela de categorias vinculada ao usuário
CREATE TABLE categorias (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL, -- Relacionamento com a tabela de usuários
    nome VARCHAR(191) NOT NULL,
    icone VARCHAR(255) NOT NULL, -- Armazena a classe do ícone
    FOREIGN KEY (usuario_id) REFERENCES users (id) ON DELETE CASCADE,
    UNIQUE (usuario_id, nome) -- Garante que um usuário não crie categorias duplicadas
);


-- Comando pra deletar categorias já existentes
DELETE FROM categorias;

-- Inserção de categorias predefinidas para todos os usuários
INSERT INTO categorias (usuario_id, nome, icone)
SELECT u.id, tmp.nome, tmp.icone
FROM users u  
JOIN (
    SELECT 'Moradia' AS nome, 'fi-sr-home' AS icone UNION ALL
    SELECT 'Beleza', 'fi-br-scissors' UNION ALL
    SELECT 'Telefone', 'fi-br-smartphone' UNION ALL
    SELECT 'Fatura', 'fi-sr-file-invoice-dollar' UNION ALL
    SELECT 'Transferência', 'fi-br-money-coin-transfer' UNION ALL
    SELECT 'Viagem Aérea', 'fi-ss-plane-alt' UNION ALL
    SELECT 'Viagem de Ônibus', 'fi-ss-bus-alt' UNION ALL
    SELECT 'Ferramenta', 'fi-ss-wrench-alt' UNION ALL
    SELECT 'Mecânica', 'fi-ss-car-mechanic' UNION ALL
    SELECT 'Supermercado', 'fi-sr-shopping-cart' UNION ALL
    SELECT 'Carteira', 'fi-sr-wallet' UNION ALL
    SELECT 'Videogame', 'fi-sr-gamepad' UNION ALL
    SELECT 'Fast Food', 'fi-ss-hotdog' UNION ALL
    SELECT 'Médico', 'fi-sr-user-md' UNION ALL
    SELECT 'Animal - Cão', 'fi-sr-dog-leashed' UNION ALL
    SELECT 'Animal - Brinquedos', 'fi-sr-bone' UNION ALL
    SELECT 'Animal - Gato', 'fi-sr-cat' UNION ALL
    SELECT 'Computador', 'fi-sr-devices' UNION ALL
    SELECT 'Livro', 'fi-ss-book-alt'
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM categorias c WHERE c.usuario_id = u.id AND c.nome = tmp.nome
);


-- Criação da tabela de transações
CREATE TABLE transacoes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    categoria_id BIGINT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias (id) ON DELETE CASCADE,
    icone VARCHAR(255) -- Pode armazenar a classe do ícone, se necessário
);

-- Criação da tabela de entradas no calendário
CREATE TABLE entradas_calendario (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('Receita', 'Despesa') NOT NULL,
    categoria_id BIGINT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data DATE NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias (id) ON DELETE CASCADE
);

-- Criação da tabela de histórico de transações
CREATE TABLE historico_transacoes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    transacao_id BIGINT NOT NULL,
    usuario_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    categoria_id BIGINT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data TIMESTAMP NOT NULL,
    modificado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transacao_id) REFERENCES transacoes (id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias (id) ON DELETE CASCADE
);

-- Criação da tabela de vencimentos
CREATE TABLE vencimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    descricao VARCHAR(255),
    data_vencimento DATE,
    valor DECIMAL(10, 2),
    categoria VARCHAR(50),
    status VARCHAR(20),
    FOREIGN KEY (usuario_id) REFERENCES users (id) ON DELETE CASCADE
);


