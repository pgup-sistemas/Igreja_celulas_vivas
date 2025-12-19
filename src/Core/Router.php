<?php

namespace Src\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH);
        
        // Remover o caminho base se existir (ex: /igreja/public)
        // $_SERVER['SCRIPT_NAME'] = /igreja/public/index.php
        // dirname() = /igreja/public
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $scriptDir = dirname($scriptName);
        
        // Normalizar scriptDir
        $scriptDir = rtrim($scriptDir, '/');
        if ($scriptDir === '' || $scriptDir === '.' || $scriptDir === '\\') {
            $scriptDir = '';
        } else {
            $scriptDir = '/' . ltrim($scriptDir, '/');
        }
        
        // Normalizar path
        $path = rtrim($path, '/') ?: '/';
        
        // Se o path começa com o scriptDir, removê-lo
        if ($scriptDir && strpos($path, $scriptDir) === 0) {
            $path = substr($path, strlen($scriptDir));
            $path = $path ?: '/';
        }

        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            http_response_code(404);
            echo 'Página não encontrada';
            return;
        }

        call_user_func($handler);
    }
}

