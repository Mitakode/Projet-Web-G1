<?php

namespace App\Model;

use PDO;

class OffreModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Compte combien d'offres existent dans la base de données au total
     */
    public function getTotalOffres()
    {
        $query = $this->pdo->query("SELECT COUNT(*) as total FROM Offre");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    /**
     * Récupère un certain nombre d'offres (limit) à partir d'un certain point (offset)
     */
    public function getOffresPaginated($limit, $offset)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Offre ORDER BY Id_offre DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
