# Regras de Negócio - Sistema de Gestão de Células

## Estrutura de Relacionamentos

### 1. Usuários (`usuarios`)
- Tabela base de autenticação do sistema
- Perfis: `admin` ou `lider`
- Campos: id, nome, email, senha, perfil, ativo, data_criacao

### 2. Líderes (`lideres`)
- Tabela que referencia `usuarios`
- Um líder **sempre** tem um `usuario_id` vinculado
- Campos: id, nome, telefone, usuario_id
- **IMPORTANTE**: Nem todo usuário com perfil 'lider' precisa estar na tabela `lideres`, mas quando um usuário é criado/editado com perfil 'lider', um líder é criado automaticamente

### 3. Células (`celulas`)
- Referencia `lideres` via `lider_id` (FOREIGN KEY)
- **NÃO** referencia `usuarios` diretamente
- Campos: id, nome, congregacao_id, lider_id, cidade, bairro, zona, ponto_referencia, ativa

## Regras de Negócio Implementadas

### Criação de Usuário

**Quando um usuário é criado com perfil 'lider':**
1. O usuário é inserido na tabela `usuarios`
2. **Automaticamente** um registro é criado na tabela `lideres` vinculado a esse usuário
3. O nome do líder é copiado do nome do usuário

**Exemplo:**
```sql
-- Usuário criado
INSERT INTO usuarios (nome, email, senha, perfil, ativo) 
VALUES ('João Silva', 'joao@igreja.com', 'hash', 'lider', 1);
-- ID criado: 5

-- Líder criado automaticamente
INSERT INTO lideres (nome, usuario_id) 
VALUES ('João Silva', 5);
-- ID criado: 3
```

### Edição de Usuário

**Quando um usuário é editado:**
1. Se o perfil mudar de qualquer coisa para 'lider':
   - Verifica se já existe líder na tabela `lideres`
   - Se não existir, cria automaticamente
2. Se o perfil já for 'lider' e o nome mudar:
   - Atualiza o nome na tabela `lideres` também

### Criação de Célula

**Quando uma célula é criada:**
1. O formulário mostra apenas líderes da tabela `lideres` (não usuários diretamente)
2. Se não houver líderes na tabela `lideres`, mas houver usuários com perfil 'lider':
   - Cria automaticamente líderes para todos os usuários com perfil 'lider'
   - Depois mostra no formulário
3. O `lider_id` usado é sempre o ID da tabela `lideres`, nunca o ID de `usuarios`

### Sincronização

**Script de sincronização:**
- `database/sincronizar_lideres.php` - Cria líderes para todos os usuários com perfil 'lider' que ainda não têm líder

## Fluxo Completo

### Cenário 1: Criar usuário líder e depois criar célula

1. Admin cria usuário com perfil 'lider'
   - ✅ Usuário criado em `usuarios`
   - ✅ Líder criado automaticamente em `lideres`

2. Admin cria célula
   - ✅ Formulário mostra o líder criado
   - ✅ Célula é vinculada ao líder (não ao usuário diretamente)

### Cenário 2: Usuários líderes já existem, mas não têm líder

1. Admin acessa formulário de criar célula
   - ✅ Sistema detecta que há usuários com perfil 'lider' sem líder
   - ✅ Cria automaticamente líderes para todos
   - ✅ Mostra no formulário

2. Admin seleciona líder e cria célula
   - ✅ Célula vinculada corretamente ao líder

## Validações

### Foreign Keys
- `celulas.lider_id` → `lideres.id` (obrigatório se fornecido)
- `celulas.congregacao_id` → `congregacoes.id` (opcional)
- `lideres.usuario_id` → `usuarios.id` (obrigatório)

### Regras de Integridade
- Um usuário com perfil 'lider' pode ter apenas um registro na tabela `lideres`
- Um líder pode ter múltiplas células
- Se um usuário for desativado, o líder também não aparecerá (WHERE u.ativo = 1)

## Solução de Problemas

### Problema: Líderes não aparecem no formulário

**Solução:**
1. Execute o script de sincronização: `http://localhost/igreja/database/sincronizar_lideres.php`
2. Ou simplesmente acesse o formulário novamente - o sistema criará automaticamente

### Problema: Erro ao criar célula com líder

**Verificações:**
1. O líder existe na tabela `lideres`?
2. O usuário vinculado ao líder está ativo?
3. A foreign key está correta?

## Arquivos Modificados

- `src/Controllers/UsuarioController.php` - Cria líder automaticamente ao criar/editar usuário
- `src/Controllers/CelulaController.php` - Cria líderes automaticamente se necessário e sempre busca da tabela `lideres`
- `database/sincronizar_lideres.php` - Script para sincronizar líderes existentes

