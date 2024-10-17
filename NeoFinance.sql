-- Criação do banco de dados
CREATE DATABASE finance;

USE finance;

-- Criação da tabela de usuários
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    saldo DECIMAL(10, 2) DEFAULT 0.00,  -- Campo para armazenar o saldo do usuário
    saldo_inicial_adicionado TINYINT(1) DEFAULT 0,  -- Para marcar se o saldo inicial foi adicionado
    remember_token VARCHAR(64) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
    usuario_id INT NOT NULL,
    nome VARCHAR(191) NOT NULL,
    icone VARCHAR(255) NOT NULL,
    excluida BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (usuario_id) REFERENCES users (id) ON DELETE CASCADE,
    UNIQUE (usuario_id, nome) -- Garante que um usuário não crie categorias duplicadas apenas se não estiver excluída
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
    tipo_transacao ENUM('Receita', 'Despesa') NOT NULL,
    categoria VARCHAR(50),
    status VARCHAR(20),
    FOREIGN KEY (usuario_id) REFERENCES users (id) ON DELETE CASCADE
);

-- Criação da tabela de Metas
CREATE TABLE metas (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_meta VARCHAR(255) NOT NULL,
    valor_alvo DECIMAL(10, 2) NOT NULL,
    valor_atual DECIMAL(10, 2) DEFAULT 0.00,  -- Valor atual economizado
    data_limite DATE,  -- Data limite para atingir a meta
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES users (id) ON DELETE CASCADE
);

-- Criação do Trigger para inserir categorias predefinidas para novos usuários
DELIMITER //

CREATE TRIGGER after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO categorias (usuario_id, nome, icone)
    VALUES
        (NEW.id, 'Moradia', 'fi-sr-home'),
        (NEW.id, 'Beleza', 'fi-br-scissors'),
        (NEW.id, 'Telefone', 'fi-br-smartphone'),
        (NEW.id, 'Fatura', 'fi-sr-file-invoice-dollar'),
        (NEW.id, 'Transferência', 'fi-br-money-coin-transfer'),
        (NEW.id, 'Viagem Aérea', 'fi-ss-plane-alt'),
        (NEW.id, 'Viagem de Ônibus', 'fi-ss-bus-alt'),
        (NEW.id, 'Ferramenta', 'fi-ss-wrench-alt'),
        (NEW.id, 'Mecânica', 'fi-ss-car-mechanic'),
        (NEW.id, 'Supermercado', 'fi-sr-shopping-cart'),
        (NEW.id, 'Carteira', 'fi-sr-wallet'),
        (NEW.id, 'Videogame', 'fi-sr-gamepad'),
        (NEW.id, 'Fast Food', 'fi-ss-hotdog'),
        (NEW.id, 'Médico', 'fi-sr-user-md'),
        (NEW.id, 'Animal - Cão', 'fi-sr-dog-leashed'),
        (NEW.id, 'Animal - Brinquedos', 'fi-sr-bone'),
        (NEW.id, 'Animal - Gato', 'fi-sr-cat'),
        (NEW.id, 'Computador', 'fi-sr-devices'),
        (NEW.id, 'Livro', 'fi-ss-book-alt');
END;
//

DELIMITER ;
