-- Esquema inicial conforme SPEC
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    perfil ENUM('admin','lider') NOT NULL DEFAULT 'lider',
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    data_criacao DATETIME NOT NULL
);

CREATE TABLE congregacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    ativa TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE lideres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    telefone VARCHAR(30),
    usuario_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE celulas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    congregacao_id INT,
    lider_id INT,
    cidade VARCHAR(80),
    bairro VARCHAR(80),
    zona VARCHAR(40),
    ponto_referencia VARCHAR(180),
    ativa TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (congregacao_id) REFERENCES congregacoes(id),
    FOREIGN KEY (lider_id) REFERENCES lideres(id)
);

CREATE TABLE reunioes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    celula_id INT NOT NULL,
    nome_celula VARCHAR(120) NOT NULL,
    lider_nome VARCHAR(120) NOT NULL,
    anfitriao_nome VARCHAR(120) NOT NULL,
    telefone_lider VARCHAR(30) NOT NULL,
    data DATE NOT NULL,
    dia_semana VARCHAR(20) NOT NULL,
    horario TIME NOT NULL,
    cadastrados INT NOT NULL DEFAULT 0,
    presentes INT NOT NULL DEFAULT 0,
    visitantes INT NOT NULL DEFAULT 0,
    mda INT NOT NULL DEFAULT 0,
    visitas INT NOT NULL DEFAULT 0,
    culto_celebracao INT NOT NULL DEFAULT 0,
    aceitacao INT NOT NULL DEFAULT 0,
    oferta DECIMAL(10,2) NOT NULL DEFAULT 0,
    observacoes TEXT,
    criado_por INT NOT NULL,
    criado_em DATETIME NOT NULL,
    UNIQUE KEY uniq_reuniao (celula_id, data, horario),
    FOREIGN KEY (celula_id) REFERENCES celulas(id),
    FOREIGN KEY (criado_por) REFERENCES usuarios(id)
);

INSERT INTO usuarios (nome, email, senha, perfil, ativo, data_criacao)
VALUES ('Admin', 'admin@igreja.com', '$2y$10$dXJHx4JtQKE.LVDckLZ/WO3xvTR0K6lG0XspvtokhCDLQqQ7Hd8k6', 'admin', 1, NOW());

