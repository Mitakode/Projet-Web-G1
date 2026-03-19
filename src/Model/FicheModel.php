<?php

namespace App\Model;

use PDO;
use App\Core\Database;

class FicheModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }


    public function getUserById($userId)
    {
        $stmt = $this->pdo->prepare("SELECT Nom, Prenom, Email FROM Utilisateur WHERE Id_user = :id_user");
        $stmt->bindValue(':id_user', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getTotalCandidatures($userId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM Candidater WHERE Id_user = :id_user");
        $stmt->bindValue(':id_user', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }


    public function getCandidaturesPaginated($userId, $limit, $offset)
    {

        $sql = "SELECT c.*, o.Titre AS titre_offre, o.Description AS description, e.Nom AS nom_entreprise
                FROM Candidater c
                JOIN Offre o ON c.Id_offre = o.Id_offre
                LEFT JOIN Entreprise e ON o.Id_entreprise = e.Id_entreprise
                WHERE c.Id_user = :id_user 
                ORDER BY c.Date_ DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_user', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
