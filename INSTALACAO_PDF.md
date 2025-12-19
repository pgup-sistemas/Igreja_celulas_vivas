# Instalação da Biblioteca PDF (TCPDF)

Para habilitar a exportação PDF dos relatórios, é necessário instalar a biblioteca TCPDF.

## Opção 1: Download Manual (Recomendado)

1. Baixe o TCPDF do repositório oficial:
   - Acesse: https://github.com/tecnickcom/TCPDF
   - Clique em "Code" > "Download ZIP"
   - Ou baixe diretamente: https://github.com/tecnickcom/TCPDF/archive/refs/heads/main.zip

2. Extraia o arquivo ZIP

3. Copie a pasta `tcpdf` para o diretório `vendor/tecnickcom/` do projeto:
   ```
   igreja/
   └── vendor/
       └── tecnickcom/
           └── tcpdf/
               ├── tcpdf.php
               └── ... (outros arquivos)
   ```

4. Certifique-se de que o arquivo `vendor/tecnickcom/tcpdf/tcpdf.php` existe

## Opção 2: Via Composer (Se disponível)

Se você tiver Composer instalado:

```bash
composer require tecnickcom/tcpdf
```

## Verificação

Após a instalação, acesse a página de relatórios e clique em "Exportar PDF". 
Se a biblioteca estiver instalada corretamente, o PDF será gerado automaticamente.

## Estrutura Final Esperada

```
igreja/
├── vendor/
│   └── tecnickcom/
│       └── tcpdf/
│           ├── tcpdf.php
│           ├── tcpdf_parser.php
│           ├── tcpdf_import.php
│           └── ... (outros arquivos)
├── src/
│   └── Core/
│       └── PdfGenerator.php
└── ...
```

## Nota

Se a biblioteca não estiver instalada, o sistema mostrará uma mensagem de erro ao tentar exportar PDF, mas continuará funcionando normalmente para CSV e outras funcionalidades.

