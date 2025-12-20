# Sistema de Gestão de Células

## Funcionalidades

- Gerenciamento de usuários e células
- Registro de reuniões semanais
- Relatórios em PDF
- Dashboard administrativo
- **Onboarding intuitivo** para novos usuários
- **Recuperação de senha** segura sem necessidade de contato com admin

## Recuperação de Senha

O sistema permite que usuários recuperem suas senhas de forma independente:

- **Acesso**: Link "Esqueci minha senha" na tela de login
- **Validação**: Usa dados pessoais (nome + telefone para líderes)
- **Segurança**: Não requer envio de e-mail, validação local
- **Fluxo**: E-mail → Verificação de identidade → Nova senha

Para líderes: valida nome + telefone cadastrado
Para admins: valida nome (e-mail já confirmado)

## Instalação

1. Configure o banco de dados MySQL
2. Execute o schema inicial: `database/schema.sql`
3. Execute a migração do onboarding: `database/add_onboarding_field.sql`
4. Execute a migração de preferências: `database/add_show_onboarding_field.sql`
5. Configure o arquivo `config/config.php`

## Onboarding

O sistema inclui um tutorial interativo para novos usuários:

- **Acesso**: Disponível via menu "Como Usar" ou automaticamente no primeiro login
- **Funcionalidades**: 5 passos guiados cobrindo navegação, registro de reuniões e recursos
- **Responsivo**: Funciona perfeitamente em desktop e mobile
- **Interativo**: Progresso visual e navegação intuitiva

Para novos usuários, o onboarding é exibido automaticamente no primeiro login. - Igreja

Sistema web responsivo para registro, gestão e acompanhamento das células da igreja, permitindo que líderes lancem dados pelo celular e que o pastor/admin acompanhe indicadores consolidados.

## 🚀 Guia Completo de Instalação e Funcionamento

### 📋 Pré-requisitos

Antes de começar, certifique-se de ter instalado:

- **XAMPP** (ou similar) com:
  - Apache
  - MySQL/MariaDB
  - PHP 7.4 ou superior
- **Navegador web** (Chrome, Firefox, Edge)
- **Git** (opcional, para clonar o repositório)

### 📥 Instalação Passo a Passo

#### 1. **Download do Sistema**

**Opção A: Clonando do GitHub**
```bash
cd C:\xampp\htdocs
git clone https://github.com/pgup-sistemas/Igreja_celulas_vivas.git igreja
```

**Opção B: Download Manual**
- Baixe o ZIP do repositório: https://github.com/pgup-sistemas/Igreja_celulas_vivas/archive/main.zip
- Extraia para `C:\xampp\htdocs\igreja`

#### 2. **Configuração do Banco de Dados**

**2.1. Inicie o XAMPP**
- Abra o painel de controle do XAMPP
- Inicie os módulos **Apache** e **MySQL**

**2.2. Crie o Banco de Dados**
- Acesse: http://localhost/phpmyadmin
- Clique em "Novo" (ou "New" em inglês)
- Nome do banco: `igreja_celulas`
- Conjunto de caracteres: `utf8mb4_general_ci`
- Clique em "Criar"

**2.3. Execute os Scripts SQL**

Execute os scripts na seguinte ordem:

**Primeiro: `database/schema.sql`**
- Abra o arquivo `database/schema.sql`
- Copie todo o conteúdo
- Cole no phpMyAdmin (na aba SQL do banco `igreja_celulas`)
- Clique em "Executar"

**Segundo: `database/migrations.sql`**
- Abra o arquivo `database/migrations.sql`
- Copie todo o conteúdo
- Cole no phpMyAdmin
- Clique em "Executar"

**Terceiro: `database/create_database.php` (opcional)**
- Este script cria o banco automaticamente
- Acesse: http://localhost/igreja/database/create_database.php
- Verifique se não há erros

#### 3. **Configuração do Sistema**

**3.1. Arquivo de Configuração**
- Copie o arquivo `config/config.example.php` para `config/config.php`
- Edite `config/config.php` com suas configurações:

```php
<?php
return [
    'db' => [
        'host' => 'localhost',
        'database' => 'igreja_celulas',
        'username' => 'root',  // Seu usuário MySQL
        'password' => '',      // Sua senha MySQL
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'name' => 'Sistema de Gestão de Células',
        'url' => 'http://localhost/igreja',
        'timezone' => 'America/Sao_Paulo'
    ]
];
```

**3.2. Permissões de Arquivos (Windows)**
- Certifique-se que a pasta `storage/logs/` tem permissões de escrita
- O XAMPP geralmente já tem as permissões corretas

#### 4. **Teste da Instalação**

**4.1. Verifique o Banco de Dados**
- Acesse: http://localhost/phpmyadmin
- Banco `igreja_celulas` deve ter as tabelas:
  - `usuarios`
  - `congregacoes`
  - `celulas`
  - `reunioes`

**4.2. Teste o Sistema**
- Acesse: http://localhost/igreja/public/
- Deve redirecionar para o login

### 🔐 Primeiro Acesso

**Usuário Admin Padrão:**
- **Email**: admin@igreja.com
- **Senha**: admin123

**⚠️ IMPORTANTE:**
1. Faça login com essas credenciais
2. Vá em **Admin → Usuários**
3. Altere a senha do admin imediatamente!

### 🌐 URLs do Sistema

#### URLs Principais
- **Login**: `http://localhost/igreja/public/login`
- **Home (Líder)**: `http://localhost/igreja/public/home`
- **Admin**: `http://localhost/igreja/public/admin`

#### URLs Alternativas (se mod_rewrite não funcionar)
- **Login**: `http://localhost/igreja/public/index.php/login`
- **Home**: `http://localhost/igreja/public/index.php/home`
- **Admin**: `http://localhost/igreja/public/index.php/admin`

### 📁 Estrutura Completa do Projeto

```
igreja/
├── config/
│   ├── config.example.php    # Template de configuração
│   └── config.php           # Arquivo de configuração (criar)
├── database/
│   ├── check_mysql.php      # Verifica conexão MySQL
│   ├── create_database.php  # Cria banco automaticamente
│   ├── fix_admin_user.php   # Corrige usuário admin
│   ├── importar_reunioes.php # Importa dados de reuniões
│   ├── migrations.sql       # Scripts adicionais do banco
│   └── schema.sql          # Estrutura inicial do banco
├── public/
│   ├── .htaccess           # Regras de reescrita URL
│   ├── index.php          # Ponto de entrada do sistema
│   ├── debug_*.php        # Arquivos de debug/teste
│   └── test_*.php         # Arquivos de teste
├── src/
│   ├── Controllers/       # Controladores MVC
│   │   ├── AdminController.php
│   │   ├── AuthController.php
│   │   ├── CelulaController.php
│   │   ├── CongregacaoController.php
│   │   ├── FechamentoController.php
│   │   ├── HomeController.php
│   │   ├── RelatorioController.php
│   │   ├── ReuniaoController.php
│   │   └── UsuarioController.php
│   ├── Core/              # Classes base
│   │   ├── Auth.php       # Autenticação
│   │   ├── Controller.php # Classe base controller
│   │   ├── Database.php   # Conexão banco de dados
│   │   ├── Logger.php     # Sistema de logs
│   │   ├── Model.php      # Classe base model
│   │   ├── PdfGenerator.php # Geração de PDFs
│   │   └── Router.php     # Roteamento
│   ├── Models/            # Modelos de dados
│   │   ├── Celula.php
│   │   ├── Congregacao.php
│   │   ├── Reuniao.php
│   │   └── User.php
│   └── Views/             # Templates
│       ├── layout.php     # Layout principal
│       ├── admin/         # Views do admin
│       ├── auth/          # Views de autenticação
│       ├── home/          # Views da home
│       └── reunioes/      # Views de reuniões
├── storage/
│   └── logs/              # Arquivos de log
├── vendor/                # Dependências (Composer)
├── .gitignore            # Arquivos ignorados pelo Git
├── composer.json         # Configuração Composer
├── README.md             # Este arquivo
└── URLS_CORRETAS.md      # Guia de URLs
```

### ⚙️ Tecnologias Utilizadas

- **Backend**: PHP 7.4+ (MVC personalizado)
- **Banco de Dados**: MySQL/MariaDB
- **Frontend**: Bootstrap 5 + Bootstrap Icons
- **Fonte**: Roboto (Google Fonts)
- **Autenticação**: Sessões PHP + bcrypt
- **Relatórios**: TCPDF para geração de PDFs
- **Versionamento**: Git + GitHub

### 🎯 Funcionalidades

#### 👤 Perfil Líder
- ✅ Registrar novas reuniões
- ✅ Visualizar células vinculadas
- ✅ Histórico de reuniões

#### 👨‍💼 Perfil Admin (Pastor/Coordenação)
- ✅ **Usuários**: Criar, editar, ativar/desativar, redefinir senhas
- ✅ **Congregações**: Gerenciar congregações
- ✅ **Células**: Criar e gerenciar células
- ✅ **Relatórios**: Dashboard com indicadores consolidados
- ✅ **Fechamentos**: Controle de fechamentos mensais
- ✅ **Export**: CSV e PDF dos relatórios

### 🔧 Solução de Problemas

#### ❌ Erro: "Página não encontrada"
- Verifique se o Apache está rodando
- Certifique-se que está acessando `http://localhost/igreja/public/`
- Verifique o arquivo `.htaccess`

#### ❌ Erro: "Conexão com banco falhou"
- Verifique se o MySQL está rodando
- Confirme as credenciais em `config/config.php`
- Execute `database/check_mysql.php` para testar

#### ❌ Erro: "Tabelas não existem"
- Execute novamente os scripts SQL
- Verifique se o banco `igreja_celulas` foi criado

#### ❌ Erro: "Permissões insuficientes"
- Certifique-se que as pastas têm permissões de escrita
- No Windows/XAMPP, geralmente não há problemas

### 🚀 Configuração Avançada (Opcional)

#### Virtual Host para URLs Curtas
Para usar `http://igreja.local` ao invés de `http://localhost/igreja`:

1. Edite `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
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

2. Edite `C:\Windows\System32\drivers\etc\hosts` (como Administrador):
```
127.0.0.1 igreja.local
```

3. Reinicie o Apache
4. Acesse: `http://igreja.local`

### 🌐 Migração para Produção

#### Configuração HTTPS/SSL
Para colocar o sistema em produção com HTTPS seguro:

**1. Configure SSL no Servidor**
- Instale certificado SSL (Let's Encrypt gratuito ou pago)
- Configure Virtual Host para HTTPS

**2. Atualize a Configuração**
Edite `config/config.php`:
```php
'app' => [
    'name' => 'Sistema de Gestão de Células',
    'url' => 'https://seudominio.com',  // ← Use HTTPS em produção
    'timezone' => 'America/Sao_Paulo'
]
```

**3. Force HTTPS (Opcional)**
Adicione ao `public/.htaccess`:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

**4. Checklist de Produção**
- ✅ Upload de todos os arquivos
- ✅ Configuração do banco MySQL
- ✅ Execução dos scripts SQL (`schema.sql` + `migrations.sql`)
- ✅ Configuração SSL/HTTPS
- ✅ Teste de todas as funcionalidades
- ✅ Backup automático configurado

**⚠️ Importante:** As routes do sistema são relativas e funcionam automaticamente com HTTPS. Não é necessário alterar nenhuma rota!

### 📞 Suporte

Para suporte técnico:
- **Email**: suporte@pgup.com.br
- **GitHub**: https://github.com/pgup-sistemas/Igreja_celulas_vivas
- **Desenvolvido por**: PgUp Sistemas

---

**By PgUp Sistemas** - Sistema de Gestão de Células para Igrejas
- ✅ Relatórios com filtros (mês, congregação, cidade, bairro, célula)
- ✅ Exportação CSV dos relatórios
- ✅ Exportação PDF dos relatórios (requer TCPDF)
- ✅ Fechar e reabrir meses (controle de edição)

### Perfil Líder

- ✅ Visualizar apenas suas células
- ✅ Registrar reuniões
- ✅ Consultar histórico de reuniões próprias

## Regras de Negócio

- Todos os campos quantitativos são numéricos (padrão 0)
- Validações: Presentes ≤ Cadastrados, Aceitação ≤ Visitantes
- Não permite valores negativos
- Controle de duplicidade: mesma célula + data + horário
- Mês fechado não permite edição de reuniões
- Sistema de logs para auditoria

## Desenvolvimento

### Executar servidor local

```bash
php -S localhost:8000 -t public
```

Acesse: http://localhost:8000

### Logs

Os logs são salvos em `storage/logs/app-YYYY-MM-DD.log` com níveis:
- INFO: Operações normais
- WARNING: Validações falhadas, tentativas suspeitas
- ERROR: Erros do sistema

## Exportação PDF

Para habilitar a exportação PDF, é necessário instalar a biblioteca TCPDF. 
Veja as instruções detalhadas em `INSTALACAO_PDF.md`.

**Resumo rápido:**
1. Baixe TCPDF de https://github.com/tecnickcom/TCPDF
2. Extraia e coloque em `vendor/tecnickcom/tcpdf/`
3. Certifique-se de que `vendor/tecnickcom/tcpdf/tcpdf.php` existe

## Próximos Passos (Futuro)

- Gráficos e visualizações avançadas
- App nativo
- Integração WhatsApp
- Notificações push

## Licença

Uso interno da igreja.

