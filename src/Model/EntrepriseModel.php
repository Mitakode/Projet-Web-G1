<?php

namespace App\Model;

use PDO;

class EntrepriseModel
{
    private $pdo;

    public function __construct()
    {
    $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",DB_USER,DB_PASS);
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAll(): array
    {
        $query = $this->pdo->query("SELECT * FROM Entreprise");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Entreprise WHERE Id_entreprise = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO Entreprise (Nom, Description, Email_contact, Telephone, Est_actif)
            VALUES (:nom, :description, :email_contact, :telephone, :est_actif)
        ");
        $stmt->execute([
            ':nom'          => $data['nom'],
            ':description'  => $data['description'],
            ':email_contact'=> $data['email_contact'],
            ':telephone'    => $data['telephone'] ?: null,
            ':est_actif'    => $data['est_actif'] ?? 1,
        ]);
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE Entreprise SET
                Nom           = :nom,
                Description   = :description,
                Email_contact = :email_contact,
                Telephone     = :telephone,
                Est_actif     = :est_actif
            WHERE Id_entreprise = :id
        ");
        $stmt->execute([
            ':nom'          => $data['nom'],
            ':description'  => $data['description'],
            ':email_contact'=> $data['email_contact'],
            ':telephone'    => $data['telephone'] ?: null,
            ':est_actif'    => $data['est_actif'] ?? 1,
            ':id'           => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Entreprise WHERE Id_entreprise = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
