# URLs Corretas do Sistema

## ⚠️ IMPORTANTE: Use sempre o caminho completo!

O sistema está instalado em: `C:\xampp\htdocs\igreja\`

## URLs Corretas

### Acesso Principal
- **Login**: `http://localhost/igreja/public/login`
- **Home (Líder)**: `http://localhost/igreja/public/home`
- **Admin**: `http://localhost/igreja/public/admin`

### URLs Alternativas (se mod_rewrite não estiver funcionando)
- **Login**: `http://localhost/igreja/public/index.php/login`
- **Home**: `http://localhost/igreja/public/index.php/home`
- **Admin**: `http://localhost/igreja/public/index.php/admin`

## ❌ URLs Incorretas (NÃO funcionam)

- ❌ `http://localhost/admin` - **ERRADO!**
- ❌ `http://localhost/home` - **ERRADO!**
- ❌ `http://localhost/login` - **ERRADO!**

## ✅ Como Funciona

Após fazer login, o sistema **automaticamente** redireciona para a URL correta com o caminho base:
- Admin → `http://localhost/igreja/public/admin`
- Líder → `http://localhost/igreja/public/home`

**Você não precisa digitar o caminho completo após o login!**

## 🔧 Solução: Configurar Virtual Host (Opcional)

Para usar URLs mais curtas como `http://igreja.local/admin`, configure um Virtual Host:

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
6. Acesse: `http://igreja.local/admin`

## 📝 Resumo

- **Sempre use**: `http://localhost/igreja/public/[rota]`
- **Após login**: O sistema redireciona automaticamente
- **Para URLs curtas**: Configure Virtual Host (opcional)

