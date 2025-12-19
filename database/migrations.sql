-- Migration: Adicionar tabela de fechamento mensal
CREATE TABLE IF NOT EXISTS fechamentos_mensais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mes INT NOT NULL,
    ano INT NOT NULL,
    fechado TINYINT(1) NOT NULL DEFAULT 0,
    fechado_por INT,
    fechado_em DATETIME,
    reaberto_por INT NULL,
    reaberto_em DATETIME NULL,
    UNIQUE KEY uniq_mes_ano (mes, ano),
    FOREIGN KEY (fechado_por) REFERENCES usuarios(id),
    FOREIGN KEY (reaberto_por) REFERENCES usuarios(id)
);

-- Migration: Adicionar índice para melhor performance em consultas por data
CREATE INDEX idx_reunioes_data ON reunioes(data);
CREATE INDEX idx_reunioes_celula_data ON reunioes(celula_id, data);

