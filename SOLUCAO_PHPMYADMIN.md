# Solução de Problemas - phpMyAdmin e MySQL

## Problema: Não consigo acessar o phpMyAdmin

### Soluções Rápidas

#### 1. Verificar se o MySQL está rodando

1. Abra o **XAMPP Control Panel**
2. Verifique se o **MySQL** está com status "Running" (verde)
3. Se não estiver, clique em **"Start"** ao lado do MySQL
4. Aguarde alguns segundos até aparecer "Running"

#### 2. Verificar se o Apache está rodando

1. No **XAMPP Control Panel**, verifique se o **Apache** está "Running"
2. Se não estiver, clique em **"Start"**
3. Ambos (Apache e MySQL) precisam estar rodando para acessar o phpMyAdmin

#### 3. Acessar o phpMyAdmin

- URL: `http://localhost/phpmyadmin`
- Ou: `http://127.0.0.1/phpmyadmin`

#### 4. Credenciais padrão

- **Usuário**: `root`
- **Senha**: (deixe em branco)

### Solução Alternativa: Criar Banco via Script PHP

Se o phpMyAdmin não estiver acessível, você pode criar o banco de dados usando o script fornecido:

#### Opção 1: Via Navegador
1. Acesse: `http://localhost/igreja/database/create_database.php`
2. O script criará automaticamente o banco e as tabelas

#### Opção 2: Via Linha de Comando
```bash
cd C:\xampp\htdocs\igreja
php database\create_database.php
```

#### Opção 3: Diagnóstico
1. Acesse: `http://localhost/igreja/database/check_mysql.php`
2. O script verificará o status do MySQL e mostrará problemas encontrados

### Problemas Comuns

#### Erro: "Access denied for user 'root'@'localhost'"

**Solução:**
1. Abra o XAMPP Control Panel
2. Clique em "Config" ao lado do MySQL
3. Selecione "my.ini"
4. Procure por `[mysqld]` e adicione:
   ```
   skip-grant-tables
   ```
5. Reinicie o MySQL
6. Acesse o phpMyAdmin e redefina a senha do root
7. Remova a linha `skip-grant-tables` e reinicie novamente

#### Erro: "Port 3306 is already in use"

**Solução:**
1. Outro serviço está usando a porta 3306
2. No XAMPP Control Panel, clique em "Config" > "my.ini"
3. Altere a porta de `3306` para `3307` (ou outra disponível)
4. Atualize o `config/config.php` com a nova porta:
   ```php
   'host' => '127.0.0.1:3307',
   ```

#### Erro: "MySQL service failed to start"

**Solução:**
1. Verifique se há outro MySQL instalado (pode estar em conflito)
2. Verifique os logs em `C:\xampp\mysql\data\*.err`
3. Tente reinstalar o XAMPP se o problema persistir

### Criar Banco Manualmente (via SQL)

Se preferir criar manualmente:

1. Acesse o phpMyAdmin: `http://localhost/phpmyadmin`
2. Clique em "SQL" no topo
3. Cole o conteúdo de `database/schema.sql`
4. Clique em "Executar"
5. Depois execute `database/migrations.sql`

### Verificar Status dos Serviços

#### Via PowerShell:
```powershell
Get-Service | Where-Object {$_.DisplayName -like "*MySQL*" -or $_.DisplayName -like "*Apache*"}
```

#### Via XAMPP Control Panel:
- Abra o painel e verifique os status dos módulos

### Links Úteis

- **phpMyAdmin**: http://localhost/phpmyadmin
- **Diagnóstico MySQL**: http://localhost/igreja/database/check_mysql.php
- **Criar Banco**: http://localhost/igreja/database/create_database.php
- **Sistema**: http://localhost/igreja/public/

### Contato

Se nenhuma solução funcionar, verifique:
1. Logs do MySQL em `C:\xampp\mysql\data\`
2. Logs do Apache em `C:\xampp\apache\logs\`
3. Firewall do Windows (pode estar bloqueando)

