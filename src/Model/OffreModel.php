<?php

namespace App\Model;

use PDO;

class OffreModel
{
    private $pdo;

    public function __construct()
    {
    $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",DB_USER,DB_PASS);
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Compte combien d'offres existent dans la base de données au total
     */
    public function getTotalOffres(string $search = '')
    {
        if ($search === '') {
            $query = $this->pdo->query("SELECT COUNT(*) as total FROM Offre");
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return (int) $result['total'];
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM Offre WHERE Titre LIKE :search OR Description LIKE :search");
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    /**
     * Récupère un certain nombre d'offres (limit) à partir d'un certain point (offset)
     */
    public function getOffresPaginated(int $limit, int $offset, string $search = '')
    {
        if ($search === '') {
            $stmt = $this->pdo->prepare("
                SELECT Offre.*, Entreprise.Nom AS Nom_entreprise 
                FROM Offre 
                LEFT JOIN Entreprise ON Offre.Id_entreprise = Entreprise.Id_entreprise 
                ORDER BY Offre.Id_offre DESC 
                LIMIT :limit OFFSET :offset
            ");
        } else {
            $stmt = $this->pdo->prepare("
                SELECT Offre.*, Entreprise.Nom AS Nom_entreprise 
                FROM Offre 
                LEFT JOIN Entreprise ON Offre.Id_entreprise = Entreprise.Id_entreprise 
                WHERE Offre.Titre LIKE :search OR Offre.Description LIKE :search
                ORDER BY Offre.Id_offre DESC 
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAll(): array
    {
        $query = $this->pdo->query("
            SELECT Offre.*, Entreprise.Nom AS Nom_entreprise 
            FROM Offre 
            LEFT JOIN Entreprise ON Offre.Id_entreprise = Entreprise.Id_entreprise
        ");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
