-- Adicionar campo mostrar_onboarding na tabela usuarios
ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS mostrar_onboarding TINYINT(1) NOT NULL DEFAULT 1;