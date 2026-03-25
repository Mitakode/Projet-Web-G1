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
            $stmt = $this->pdo->prepare("SELECT * FROM Offre ORDER BY Id_offre DESC LIMIT :limit OFFSET :offset");
        } else {
            $stmt = $this->pdo->prepare("SELECT * FROM Offre WHERE Titre LIKE :search OR Description LIKE :search ORDER BY Id_offre DESC LIMIT :limit OFFSET :offset");
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
            SELECT o.*, e.Nom AS Nom_entreprise 
            FROM Offre o
            LEFT JOIN Entreprise e ON o.Id_entreprise = e.Id_entreprise
        ");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Offre WHERE Id_offre = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO Offre (Titre, Description, Date_offre, Duree_mois, Nombre_place, Id_entreprise) 
            VALUES (:titre, :description, :date_offre, :duree_mois, :nombre_place, :id_entreprise)
        ");

        $stmt->execute([
            ':titre' => $_POST['titre'],
            ':description' => $_POST['description'],
            ':date_offre' => $_POST['date_offre'],
            ':duree_mois' => $_POST['duree_mois'],
            ':nombre_place' => $_POST['nombre_place'],
            ':id_entreprise' => $_POST['id_entreprise'] ?: null,
        ]);
    }

    public function update(int $id): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE Offre SET
                Titre = :titre,
                Description = :description,
                Date_offre = :date_offre,
                Duree_mois = :duree_mois,
                Nombre_place = :nombre_place,
                Id_entreprise = :id_entreprise
            WHERE Id_offre = :id
        ");

        $stmt->execute([
            ':titre' => $_POST['titre'],
            ':description' => $_POST['description'],
            ':date_offre' => $_POST['date_offre'],
            ':duree_mois' => $_POST['duree_mois'],
            ':nombre_place' => $_POST['nombre_place'],
            ':id_entreprise' => $_POST['id_entreprise'] ?: null,
            ':id' => $id,
        ]);
    }


    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Offre WHERE Id_offre = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
