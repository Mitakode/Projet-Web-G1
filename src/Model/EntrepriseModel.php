<?php

namespace App\Model;

use PDO;

class EntrepriseModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAll(): array
    {
        $query = $this->pdo->query("SELECT * FROM Entreprise");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function noterEntreprise(int $idUser, int $idEntreprise, int $note): bool
    {
        if ($note < 1 || $note > 5) {
            return false;
        }

        $sql = "
            INSERT INTO Evaluer (Id_User, Id_Entreprise, Note)
            VALUES (:idUser, :idEntreprise, :note)
            ON DUPLICATE KEY UPDATE
                Note = VALUES(Note)
        ";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':idUser' => $idUser,
            ':idEntreprise' => $idEntreprise,
            ':note' => $note,
        ]);
    }

    public function getNoteMoyenneEntreprise(int $idEntreprise): array
    {
        $sql = "
            SELECT ROUND(AVG(Note), 2) AS moyenne, COUNT(*) AS total_votes
            FROM Evaluer
            WHERE Id_Entreprise = :idEntreprise
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idEntreprise' => $idEntreprise]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'moyenne' => ($row && $row['moyenne'] !== null) ? (float) $row['moyenne'] : 0.0,
            'total_votes' => $row ? (int) $row['total_votes'] : 0,
        ];
    }


}
