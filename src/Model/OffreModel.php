<?php

namespace App\Model;

use PDO;

class OffreModel
{
    private $pdo;

    public function __construct()
    {
        // On se connecte à la BDD une seule fois quand on appelle le modèle
        $host = '127.0.0.1';
        $dbname = 'career_quest'; // Nom de ta BDD
        $user = 'root';
        $pass = 'A2#DevWeb!';
        
        $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Compte combien d'offres existent dans la base de données au total
     */
    public function getTotalOffres()
    {
        $query = $this->pdo->query("SELECT COUNT(*) as total FROM offres");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }

    /**
     * Récupère un certain nombre d'offres (limit) à partir d'un certain point (offset)
     */
    public function getOffresPaginated($limit, $offset)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM offres ORDER BY id DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}