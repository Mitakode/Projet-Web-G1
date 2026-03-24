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

    public function getByRoleandestgerepar(int $role, int $est_gere_par): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Utilisateur WHERE `Role` = :role AND est_gere_par = :est_gere_par");
        $stmt->bindValue(':role', $role, PDO::PARAM_INT);
        $stmt->bindValue(':est_gere_par', $est_gere_par, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByRole(int $role): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Utilisateur WHERE Role = :role");
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

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM Utilisateur ORDER BY Id_user ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO Utilisateur 
            (Nom, Prenom, Email, Date_naissance, Formation, Description, est_gere_par, Role) 
            VALUES 
            (:nom, :prenom, :email, :date_naissance, :formation, :description, :est_gere_par, :role)
        ");

        $stmt->execute([
            ':nom' => $data['nom'],
            ':prenom' => $data['prenom'],
            ':email' => $data['email'],
            ':date_naissance' => $data['date_naissance'],
            ':formation' => $data['formation'],
            ':description' => $data['description'],
            ':est_gere_par' => $data['est_gere_par'] ?: null,
            ':role' => $data['role'] ?? 0,
        ]);
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE Utilisateur SET
                Nom = :nom,
                Prenom = :prenom,
                Email = :email,
                Date_naissance = :date_naissance,
                Formation = :formation,
                Description = :description,
                est_gere_par = :est_gere_par,
                Role = :role
            WHERE Id_user = :id
        ");

        $stmt->execute([
            ':nom' => $data['nom'],
            ':prenom' => $data['prenom'],
            ':email' => $data['email'],
            ':date_naissance' => $data['date_naissance'],
            ':formation' => $data['formation'],
            ':description' => $data['description'],
            ':est_gere_par' => !empty($data['est_gere_par']) ? (int)$data['est_gere_par'] : null,
            ':role' => $data['role'] ?? 0,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Utilisateur WHERE Id_user = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
