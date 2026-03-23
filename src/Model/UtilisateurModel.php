<?php

namespace App\Model;

use PDO;

class UtilisateurModel
{
    private $pdo;

    public function __construct()
    {
    $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",DB_USER,DB_PASS);
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getByRole(int $role, int $est_gere_par): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Utilisateur WHERE `Role` = :role AND est_gere_par = :est_gere_par");
        $stmt->bindValue(':role', $role, PDO::PARAM_INT);
        $stmt->bindValue(':est_gere_par', $est_gere_par, PDO::PARAM_INT);
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
