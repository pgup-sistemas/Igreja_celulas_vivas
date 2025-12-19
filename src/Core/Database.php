<?php

namespace Src\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(array $config): PDO
    {
        if (self::$connection === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $config['host'],
                $config['database'],
                $config['charset'] ?? 'utf8mb4'
            );

            try {
                self::$connection = new PDO(
                    $dsn,
                    $config['user'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                // Em produção, logue o erro e mostre mensagem amigável
                die('Erro de conexão com o banco de dados.');
            }
        }

        return self::$connection;
    }
}

