-- Adicionar campo onboarding_completo na tabela usuarios
ALTER TABLE usuarios ADD COLUMN onboarding_completo TINYINT(1) NOT NULL DEFAULT 0;