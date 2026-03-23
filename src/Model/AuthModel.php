<?php

namespace App\Model;

use PDO;

class AuthModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getByEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Utilisateur WHERE Email = :email LIMIT 1");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare("SELECT 1 FROM Utilisateur WHERE Email = :email LIMIT 1");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    public function createUser(array $data): array|false
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO Utilisateur (Nom, Prenom, Date_naissance, Formation, Description, Email, Password, Role, est_gere_par)
             VALUES (:nom, :prenom, :date_naissance, :formation, :description, :email, :password, :role, :est_gere_par)"
        );

        $ok = $stmt->execute([
            'nom' => $data['Nom'],
            'prenom' => $data['Prenom'],
            'date_naissance' => $data['Date_naissance'],
            'formation' => $data['Formation'],
            'description' => $data['Description'],
            'email' => $data['Email'],
            'password' => $data['Password'],
            'role' => $data['Role'],
            'est_gere_par' => $data['est_gere_par'],
        ]);

        if (!$ok) {
            return false;
        }

        return $this->getByEmail($data['Email']);
    }
}
