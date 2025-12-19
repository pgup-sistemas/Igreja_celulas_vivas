# Solução de Problemas - Rotas e Acesso

## Problema: "Página não encontrada" ao acessar `http://localhost/igreja/public/`

### Soluções Rápidas

#### 1. Verificar se o mod_rewrite está habilitado

O Apache precisa ter o módulo `mod_rewrite` habilitado para o sistema de rotas funcionar.

**Como verificar e habilitar:**

1. Abra o arquivo `C:\xampp\apache\conf\httpd.conf`
2. Procure por `#LoadModule rewrite_module modules/mod_rewrite.so`
3. Remova o `#` para descomentar (deve ficar: `LoadModule rewrite_module modules/mod_rewrite.so`)
4. Procure por `<Directory "C:/xampp/htdocs">` e altere `AllowOverride None` para `AllowOverride All`
5. Reinicie o Apache no XAMPP Control Panel

#### 2. Acessar diretamente o index.php

Se o mod_rewrite não estiver funcionando, você pode acessar:

- `http://localhost/igreja/public/index.php`
- `http://localhost/igreja/public/index.php/login`

#### 3. Usar o arquivo de teste

Acesse `http://localhost/igreja/public/test.php` para verificar:
- Se o PHP está funcionando
- Informações do servidor
- Se o Router está carregando corretamente

#### 4. Configurar Virtual Host (Recomendado)

Para melhor funcionamento, configure um Virtual Host no Apache:

1. Abra `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
2. Adicione:

```apache
<VirtualHost *:80>
    ServerName igreja.local
    DocumentRoot "C:/xampp/htdocs/igreja/public"
    <Directory "C:/xampp/htdocs/igreja/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

3. Abra `C:\Windows\System32\drivers\etc\hosts` como Administrador
4. Adicione: `127.0.0.1 igreja.local`
5. Reinicie o Apache
6. Acesse: `http://igreja.local`

### URLs de Acesso

Após configurar corretamente, você pode acessar:

- **Login**: `http://localhost/igreja/public/login`
- **Home**: `http://localhost/igreja/public/home` (requer login)
- **Admin**: `http://localhost/igreja/public/admin` (requer login admin)

### Verificação Rápida

1. **Teste básico**: Acesse `http://localhost/igreja/public/test.php`
2. **Teste direto**: Acesse `http://localhost/igreja/public/index.php/login`
3. **Verificar logs**: Veja `C:\xampp\apache\logs\error.log` para erros

### Problemas Comuns

#### Erro: "Página não encontrada" mesmo com mod_rewrite habilitado

**Solução:**
- Verifique se o `.htaccess` está na pasta `public/`
- Verifique se `AllowOverride All` está configurado no Apache
- Tente acessar diretamente: `http://localhost/igreja/public/index.php/login`

#### Erro: "403 Forbidden"

**Solução:**
- Verifique as permissões da pasta
- Verifique se `Require all granted` está no Virtual Host ou Directory

#### Rotas não funcionam

**Solução:**
- Acesse `http://localhost/igreja/public/test.php` para diagnóstico
- Verifique se o Router está detectando o caminho base corretamente
- Use URLs completas: `http://localhost/igreja/public/login`

### Estrutura de Rotas

O sistema usa rotas definidas em `public/index.php`:

- `/` → Redireciona para `/login` ou `/home`/`/admin` (dependendo do perfil)
- `/login` → Página de login
- `/home` → Dashboard do líder (requer autenticação)
- `/admin` → Dashboard do admin (requer autenticação)
- `/admin/*` → Rotas administrativas

### Credenciais Padrão

- **Email**: `admin@igreja.com`
- **Senha**: `admin123`

**IMPORTANTE**: Altere a senha após o primeiro acesso!

