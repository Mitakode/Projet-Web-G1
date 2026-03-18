<?php

namespace App\Model;

use PDO;

class UtilisateurModel
{
    private $pdo;

    public function __construct()
    {
        $host   = 'db';
        $dbname = 'projet_db';
        $user   = 'projet_user';
        $pass   = 'projet_pass';

        $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getByRole(int $role): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Utilisateur WHERE Rôle = :role");
        $stmt->bindValue(':role', $role, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Utilisateur WHERE Id_user = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
