<?php

namespace App\Model;

use App\Core\InputValidator;
use InvalidArgumentException;
use PDO;

/**
 * Offer data access.
 *
 * Handles listing, searching, CRUD and wishlist-related operations.
 */
class OffreModel
{
    private $pdo;

    public function __construct()
    {
        // Create a PDO connection using config constants.
        $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Count total offers in the database, optionally filtered by a search query.
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
     * Fetch a page of offers (limit/offset), optionally filtered by search.
     */
    public function getOffresPaginated(int $limit, int $offset, string $search = '')
    {
        // Join an aggregate from Evaluer to expose average rating and total votes
        // directly on offer cards (home page).
        if ($search === '') {
            $stmt = $this->pdo->prepare("
                SELECT Offre.*, 
                       Entreprise.Nom AS Nom_entreprise,
                       COALESCE(notesAgg.note_moyenne, 0) AS note_moyenne,
                       COALESCE(notesAgg.total_votes, 0) AS total_votes
                FROM Offre
                LEFT JOIN Entreprise ON Offre.Id_entreprise = Entreprise.Id_entreprise
                LEFT JOIN (
                    SELECT Id_Entreprise,
                           ROUND(AVG(Note), 2) AS note_moyenne,
                           COUNT(*) AS total_votes
                    FROM Evaluer
                    GROUP BY Id_Entreprise
                ) notesAgg ON notesAgg.Id_Entreprise = Offre.Id_entreprise
                ORDER BY Offre.Id_offre DESC 
                LIMIT :limit OFFSET :offset
            ");
        } else {
            $stmt = $this->pdo->prepare("
                SELECT Offre.*, 
                       Entreprise.Nom AS Nom_entreprise,
                       COALESCE(notesAgg.note_moyenne, 0) AS note_moyenne,
                       COALESCE(notesAgg.total_votes, 0) AS total_votes
                FROM Offre
                LEFT JOIN Entreprise ON Offre.Id_entreprise = Entreprise.Id_entreprise
                LEFT JOIN (
                    SELECT Id_Entreprise,
                           ROUND(AVG(Note), 2) AS note_moyenne,
                           COUNT(*) AS total_votes
                    FROM Evaluer
                    GROUP BY Id_Entreprise
                ) notesAgg ON notesAgg.Id_Entreprise = Offre.Id_entreprise
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

            /**
             * Fetch all offers (no pagination).
             */
    public function getAll(): array
    {
        $query = $this->pdo->query("
            SELECT Offre.*, Entreprise.Nom AS Nom_entreprise 
            FROM Offre
            LEFT JOIN Entreprise ON Offre.Id_entreprise = Entreprise.Id_entreprise
        ");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

            /**
             * Fetch a single offer by id (includes company name and rating aggregates).
             */
    public function getById(int $id): array|false
    {
                // Same aggregate logic as in listing, used by offer detail / candidature page.
        $stmt = $this->pdo->prepare("
            SELECT Offre.*, Entreprise.Nom AS Nom_entreprise,
                   COALESCE(notesAgg.note_moyenne, 0) AS note_moyenne,
                   COALESCE(notesAgg.total_votes, 0) AS total_votes
            FROM Offre
            LEFT JOIN Entreprise ON Offre.Id_entreprise = Entreprise.Id_entreprise
            LEFT JOIN (
                SELECT Id_Entreprise,
                       ROUND(AVG(Note), 2) AS note_moyenne,
                       COUNT(*) AS total_votes
                FROM Evaluer
                GROUP BY Id_Entreprise
            ) notesAgg ON notesAgg.Id_Entreprise = Offre.Id_entreprise
            WHERE Offre.Id_offre = :id
            LIMIT 1
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create an offer.
     *
     * Uses InputValidator to enforce constraints before insert.
     */
    public function create(array $data): void
    {
        // Offer title: readable text with limited punctuation.
        $titre = InputValidator::requireString($data, 'titre', '/^[\p{L}\p{N}\s\-\'".,()!?@:\/]+$/u', 180);
        // Description: free text but restricted to a safe character set.
        $description = InputValidator::requireString($data, 'description', '/^[\p{L}\p{N}\s\-\'".,()!?@:\/]*$/u', 3000, true);
        // Remuneration: positive decimal number (up to 2 decimals).
        $remunerationRaw = InputValidator::requireString($data, 'remuneration', '/^\d{1,7}(?:\.\d{1,2})?$/', 10);
        $dateOffre = InputValidator::requireDate($data, 'date_offre');
        $dureeMois = InputValidator::getInt($data, 'duree_mois', 0, 0, 120);
        $nombrePlace = InputValidator::getInt($data, 'nombre_place', 0, 0, 10000);
        $idEntreprise = InputValidator::getInt($data, 'id_entreprise', 0, 0);

        $stmt = $this->pdo->prepare("
            INSERT INTO Offre (Titre, Description, Remuneration, Date_offre, Duree_mois, Nombre_place, Id_entreprise)
            VALUES (:titre, :description, :remuneration, :date_offre, :duree_mois, :nombre_place, :id_entreprise)
        ");
        $stmt->execute([
            ':titre'         => $titre,
            ':description'   => $description,
            ':remuneration'  => $remunerationRaw,
            ':date_offre'    => $dateOffre,
            ':duree_mois'    => $dureeMois,
            ':nombre_place'  => $nombrePlace,
            ':id_entreprise' => $idEntreprise > 0 ? $idEntreprise : null,
        ]);
    }

    /**
     * Update an offer.
     *
     * @throws InvalidArgumentException When the id is invalid.
     */
    public function update(int $id, array $data): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID offre invalide.');
        }

        // Offer title: readable text with limited punctuation.
        $titre = InputValidator::requireString($data, 'titre', '/^[\p{L}\p{N}\s\-\'".,()!?@:\/]+$/u', 180);
        // Description: free text but restricted to a safe character set.
        $description = InputValidator::requireString($data, 'description', '/^[\p{L}\p{N}\s\-\'".,()!?@:\/]*$/u', 3000, true);
        // Remuneration: positive decimal number (up to 2 decimals).
        $remunerationRaw = InputValidator::requireString($data, 'remuneration', '/^\d{1,7}(?:\.\d{1,2})?$/', 10);
        $dateOffre = InputValidator::requireDate($data, 'date_offre');
        $dureeMois = InputValidator::getInt($data, 'duree_mois', 0, 0, 120);
        $nombrePlace = InputValidator::getInt($data, 'nombre_place', 0, 0, 10000);
        $idEntreprise = InputValidator::getInt($data, 'id_entreprise', 0, 0);

        $stmt = $this->pdo->prepare("
            UPDATE Offre SET
                Titre         = :titre,
                Description   = :description,
                Remuneration  = :remuneration,
                Date_offre    = :date_offre,
                Duree_mois    = :duree_mois,
                Nombre_place  = :nombre_place,
                Id_entreprise = :id_entreprise
            WHERE Id_offre = :id
        ");
        $stmt->execute([
            ':titre'         => $titre,
            ':description'   => $description,
            ':remuneration'  => $remunerationRaw,
            ':date_offre'    => $dateOffre,
            ':duree_mois'    => $dureeMois,
            ':nombre_place'  => $nombrePlace,
            ':id_entreprise' => $idEntreprise > 0 ? $idEntreprise : null,
            ':id'            => $id,
        ]);
    }

    /**
     * Delete an offer.
     */
    public function delete(int $id): array|false
    {
        $stmt = $this->pdo->prepare("DELETE FROM Offre WHERE Id_offre = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if an offer is in a user's wishlist.
     */
    public function isInWishlist(int $idOffre, int $idUser)
    {
        $sql = "SELECT 1 FROM Wishlist WHERE Id_offre = :idOffre AND Id_user = :idUser LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['idOffre' => $idOffre, 'idUser' => $idUser]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all wishlist entries for a given user.
     */
    public function getWishlist(int $idUser): array
    {
        $sql = "SELECT * FROM Wishlist WHERE Id_user = :idUser";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['idUser' => $idUser]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add an offer to a user's wishlist.
     */
    public function addWishlist(int $idOffre, int $idUser): bool
    {
        $sql = "INSERT INTO Wishlist (Id_user, Id_offre) VALUES (:idUser, :idOffre)";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(['idUser' => $idUser, 'idOffre' => $idOffre]);
    }

    /**
     * Remove an offer from a user's wishlist.
     */
    public function removeFromWishlist(int $idUser, int $idOffre): bool
    {
        $sql = "DELETE FROM Wishlist WHERE Id_user = :idUser AND Id_offre = :idOffre";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute(['idUser' => $idUser, 'idOffre' => $idOffre]);
    }
}
