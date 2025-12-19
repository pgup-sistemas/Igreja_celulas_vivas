<?php

namespace Src\Core;

class Controller
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    protected function view(string $template, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . '/../Views/' . $template . '.php';
        $layoutFile = __DIR__ . '/../Views/layout.php';

        if (!file_exists($viewFile)) {
            http_response_code(404);
            echo 'View não encontrada';
            return;
        }

        include $layoutFile;
    }

    protected function getBasePath(): string
    {
        // Detectar o caminho base de forma mais robusta
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $scriptDir = dirname($scriptName);
        
        // Normalizar: remover barras finais
        $scriptDir = rtrim($scriptDir, '/');
        if ($scriptDir === '' || $scriptDir === '.' || $scriptDir === '\\') {
            return '';
        }
        
        // Garantir que começa com /
        return '/' . ltrim($scriptDir, '/');
    }
    
    protected function redirect(string $path): void
    {
        // Se o caminho já começa com http, usar diretamente
        if (strpos($path, 'http') === 0) {
            header('Location: ' . $path);
            exit;
        }
        
        $basePath = $this->getBasePath();
        
        // Garantir que o path começa com /
        $path = '/' . ltrim($path, '/');
        
        // Se temos um basePath e o path não começa com ele, adicionar
        if ($basePath && strpos($path, $basePath) !== 0) {
            $path = $basePath . $path;
        }
        
        header('Location: ' . $path);
        exit;
    }
}

