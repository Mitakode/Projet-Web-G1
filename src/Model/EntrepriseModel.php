<?php

namespace App\Model;

use App\Core\InputValidator;
use InvalidArgumentException;
use PDO;

/**
 * Company data access.
 *
 * Provides CRUD and rating-related operations (Evaluer) as well as public
 * listing/search helpers.
 */
class EntrepriseModel
{
    private $pdo;

    public function __construct()
    {
        // Create a PDO connection using config constants.
        $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Fetch all companies.
     */
    public function getAll(): array
    {
        $query = $this->pdo->query("SELECT * FROM Entreprise");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check whether a user has applied to at least one offer of a company.
     *
     * IMPORTANT: $tableName must be an internal constant string (not user input).
     */
    private function hasAppliedInTable(string $tableName, int $idUser, int $idEntreprise): bool
    {
        // Look for at least one application from the user to an offer belonging
        // to the target company.
        $sql = "
            SELECT 1
            FROM {$tableName} c
            JOIN Offre o ON o.Id_offre = c.Id_offre
            WHERE c.Id_user = :idUser
              AND o.Id_entreprise = :idEntreprise
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':idUser' => $idUser,
            ':idEntreprise' => $idEntreprise,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    /**
     * Whether a user is allowed to rate a company.
     */
    public function canUserRateEntreprise(int $idUser, int $idEntreprise): bool
    {
        // This project stores applications in the Candidater table.
        return $this->hasAppliedInTable('Candidater', $idUser, $idEntreprise);
    }

    /**
     * Insert/update a company rating for a given user.
     *
     * Uses an UPSERT on (Id_User, Id_Entreprise).
     */
    public function noterEntreprise(int $idUser, int $idEntreprise, int $note): bool
    {
        if ($note < 1 || $note > 5) {
            return false;
        }

        // UPSERT: if a rating already exists for (user, company), it is replaced.
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

    /**
     * Get average rating and total vote count for a company.
     */
    public function getNoteMoyenneEntreprise(int $idEntreprise): array
    {
        // Returns rounded average + total vote count for the company.
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

    /**
     * Get the rating given by a specific user for a specific company.
     *
     * Used to pre-fill the UI.
     */
    public function getUserNoteEntreprise(int $idUser, int $idEntreprise): ?int
    {
        // Used to pre-fill the rating in the UI when the user already voted.
        $sql = "
            SELECT Note
            FROM Evaluer
            WHERE Id_User = :idUser AND Id_Entreprise = :idEntreprise
            LIMIT 1
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':idUser' => $idUser,
            ':idEntreprise' => $idEntreprise,
        ]);

        $note = $stmt->fetchColumn();
        return $note !== false ? (int) $note : null;
    }


    /**
     * Fetch a company by id.
     */
    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Entreprise WHERE Id_entreprise = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a company.
     */
    public function create(array $data): void
    {
        // Company name: letters/digits and limited punctuation.
        $nom = InputValidator::requireString($data, 'nom', '/^[\p{L}\p{N}\s\-\'".&()]+$/u', 150);
        // Free description restricted to a safe character set.
        $description = InputValidator::requireString($data, 'description', '/^[\p{L}\p{N}\s\-\'".,()!?@:\/]*$/u', 3000, true);
        $emailContact = InputValidator::requireEmail($data, 'email_contact');
        // Phone: common FR/intl patterns (+, spaces, parentheses, dashes).
        $telephone = InputValidator::requireString($data, 'telephone', '/^\+?[0-9\s().-]{10}$/', 20, true);
        $estActif = InputValidator::getInt($data, 'est_actif', 1, 0, 1);

        $stmt = $this->pdo->prepare("
            INSERT INTO Entreprise (Nom, Description, Email_contact, Telephone, Est_actif)
            VALUES (:nom, :description, :email_contact, :telephone, :est_actif)
        ");
        $stmt->execute([
            ':nom'          => $nom,
            ':description'  => $description,
            ':email_contact'=> $emailContact,
            ':telephone'    => $telephone !== '' ? $telephone : null,
            ':est_actif'    => $estActif,
        ]);
    }

    /**
     * Update a company.
     *
     * @throws InvalidArgumentException When id is invalid.
     */
    public function update(int $id, array $data): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Invalid company ID.');
        }

        // Company name: letters/digits and limited punctuation.
        $nom = InputValidator::requireString($data, 'nom', '/^[\p{L}\p{N}\s\-\'".&()]+$/u', 150);
        // Free description restricted to a safe character set.
        $description = InputValidator::requireString($data, 'description', '/^[\p{L}\p{N}\s\-\'".,()!?@:\/]*$/u', 3000, true);
        $emailContact = InputValidator::requireEmail($data, 'email_contact');
        // Phone: common FR/intl patterns (+, spaces, parentheses, dashes).
        $telephone = InputValidator::requireString($data, 'telephone', '/^\+?[0-9\s().-]{10}$/', 20, true);
        $estActif = InputValidator::getInt($data, 'est_actif', 1, 0, 1);

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
            ':nom'          => $nom,
            ':description'  => $description,
            ':email_contact'=> $emailContact,
            ':telephone'    => $telephone !== '' ? $telephone : null,
            ':est_actif'    => $estActif,
            ':id'           => $id,
        ]);
    }

    /**
     * Delete a company by id.
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Entreprise WHERE Id_entreprise = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Search companies by name/description/contact email.
     */
    public function search(string $search): array
{
    // Search terms are provided by controller; using a prepared statement prevents injection.
    $stmt = $this->pdo->prepare("
        SELECT * FROM Entreprise 
        WHERE Nom LIKE :s 
        OR Description LIKE :s 
        OR Email_contact LIKE :s
    ");
    $stmt->execute([':s' => '%' . $search . '%']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch offers belonging to a company.
 */
public function getOffresByEntreprise(int $id): array
{
    $stmt = $this->pdo->prepare("
        SELECT * FROM Offre WHERE Id_entreprise = :id
    ");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Compute basic company stats (applications count and rating average).
 */
public function getStats(int $id): array
{
    // Number of candidatures across all offers of the company.
    $stmt = $this->pdo->prepare("
        SELECT COUNT(*) as nb_candidatures
        FROM Candidater c
        JOIN Offre o ON c.Id_offre = o.Id_offre
        WHERE o.Id_entreprise = :id
    ");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $nb = $stmt->fetch(PDO::FETCH_ASSOC);

    // Rating average from Evaluer.
    $stmt2 = $this->pdo->prepare("
        SELECT AVG(Note) as moyenne
        FROM Evaluer
        WHERE Id_entreprise = :id
    ");
    $stmt2->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt2->execute();
    $moy = $stmt2->fetch(PDO::FETCH_ASSOC);

    return [
        'nb_candidatures' => $nb['nb_candidatures'] ?? 0,
        'moyenne'         => $moy['moyenne'] ? round((float)$moy['moyenne'], 1) : 'N/A'
    ];
    }
}