# Sistema de Gestão de Células - Igreja

Sistema web responsivo para registro, gestão e acompanhamento das células da igreja, permitindo que líderes lancem dados pelo celular e que o pastor/admin acompanhe indicadores consolidados.

## Tecnologias

- **Backend**: PHP 7.4+ (MVC simples)
- **Banco de Dados**: MySQL/MariaDB
- **Frontend**: Bootstrap 5 (mobile-first)
- **Autenticação**: Sessões PHP com senhas criptografadas

## Instalação

1. Clone o repositório ou extraia os arquivos
2. Configure o banco de dados em `config/config.php`
3. Execute o script SQL em `database/schema.sql` para criar as tabelas
4. Execute o script SQL em `database/migrations.sql` para criar tabelas adicionais
5. Configure seu servidor web para apontar para a pasta `public/`

### Usuário Admin Padrão

- **Email**: admin@igreja.com
- **Senha**: admin123

**IMPORTANTE**: Altere a senha após o primeiro acesso!

## Estrutura do Projeto

```
igreja/
├── config/          # Configurações
├── database/        # Scripts SQL
├── public/          # Ponto de entrada (index.php)
├── src/
│   ├── Controllers/ # Controladores MVC
│   ├── Core/        # Classes base (Database, Router, Auth, Logger)
│   ├── Models/      # Modelos de dados
│   └── Views/       # Templates de visualização
└── storage/
    └── logs/        # Arquivos de log
```

## Funcionalidades

### Perfil Admin (Pastor/Coordenação)

- ✅ Gerenciar usuários (criar, editar, ativar/desativar, redefinir senha)
- ✅ Gerenciar congregações
- ✅ Gerenciar células
- ✅ Visualizar dashboard com indicadores consolidados
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

