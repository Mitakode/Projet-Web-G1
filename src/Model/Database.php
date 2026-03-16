<?php

namespace App\Model;

use PDO;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host   = $_ENV['DB_HOST'] ?? 'localhost';
            $port   = $_ENV['DB_PORT'] ?? '3306';
            $dbname = $_ENV['DB_NAME'] ?? 'projet_db';
            $user   = $_ENV['DB_USER'] ?? 'projet_user';
            $pass   = $_ENV['DB_PASS'] ?? 'projet_pass';

            try {
                self::$instance = new PDO(
                    "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
                    $user,
                    $pass
                );
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                throw new \PDOException('Database connection failed: ' . $e->getMessage(), (int)$e->getCode());
            }
        }

        return self::$instance;
    }
}
