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

-- Migration: Adicionar campos de onboarding na tabela usuarios
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS onboarding_completo TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS mostrar_onboarding TINYINT(1) NOT NULL DEFAULT 1;

