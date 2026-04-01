<?php

namespace App\Model;

use App\Core\InputValidator;
use InvalidArgumentException;
use PDO;

/**
 * User data access.
 *
 * Provides CRUD operations and helper queries for role-based screens.
 */
class UtilisateurModel
{
    private $pdo;

    public function __construct()
    {
    // Create a PDO connection using config constants.
    $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",DB_USER,DB_PASS);
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Fetch users by role and manager id (est_gere_par).
     */
    public function getByRoleAndEstGerePar(int $role, int $est_gere_par): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Utilisateur WHERE `Role` = :role AND est_gere_par = :est_gere_par");
        $stmt->bindValue(':role', $role, PDO::PARAM_INT);
        $stmt->bindValue(':est_gere_par', $est_gere_par, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch users by role.
     */
    public function getByRole(int $role): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Utilisateur WHERE Role = :role");
        $stmt->bindValue(':role', $role, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
        /**
         * Fetch a user by id.
         */
    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Utilisateur WHERE Id_user = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

        /**
         * Fetch all users.
         */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM Utilisateur ORDER BY Id_user ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        /**
         * Create a user (student/pilot/admin).
         */
    public function create(array $data): void
    {
            // Last/first name: letters, spaces, dashes and apostrophes.
        $nom = InputValidator::requireString($data, 'nom', '/^[\p{L}\s\-\']+$/u', 100);
        $prenom = InputValidator::requireString($data, 'prenom', '/^[\p{L}\s\-\']+$/u', 100);
        $dateNaissance = InputValidator::requireDate($data, 'date_naissance');
            // Formation: academic text with simple punctuation.
        $formation = InputValidator::requireString($data, 'formation', '/^[\p{L}\p{N}\s\-\'".,()\/]+$/u', 150);
            // Description: free text but restricted to avoid unwanted characters.
        $description = InputValidator::requireString($data, 'description', '/^[\p{L}\p{N}\s\-\'".,()!?@:\/]*$/u', 2000, true);
        $email = InputValidator::requireEmail($data, 'email');
            // Password: 8..255 characters, no binary null byte.
        $passwordRaw = InputValidator::requireString($data, 'Password', '/^[^\x00]{8,255}$/', 255);
        $role = InputValidator::getInt($data, 'role', 0, 0, 2);
        $estGerePar = InputValidator::getInt($data, 'est_gere_par', 0, 0);

        $stmt = $this->pdo->prepare(
            "INSERT INTO Utilisateur (Nom, Prenom, Date_naissance, Formation, Description, Email, Password, Role, est_gere_par)
            VALUES (:nom, :prenom, :date_naissance, :formation, :description, :email, :password, :role, :est_gere_par)"
        );

        $stmt->execute([
            'nom'            => $nom,
            'prenom'         => $prenom,
            'date_naissance' => $dateNaissance,
            'formation'      => $formation,
            'description'    => $description,
            'email'          => $email,
            // Store a one-way hash.
            'password'       => password_hash($passwordRaw, PASSWORD_DEFAULT),
            'role'           => $role,
            'est_gere_par'   => $estGerePar > 0 ? $estGerePar : null,
        ]);
    }

    /**
     * Update a user.
     *
     * @throws InvalidArgumentException When id is invalid.
     */
    public function update(int $id, array $data): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Invalid user ID.');
        }

        // Last/first name: letters, spaces, dashes and apostrophes.
        $nom = InputValidator::requireString($data, 'nom', '/^[\p{L}\s\-\']+$/u', 100);
        $prenom = InputValidator::requireString($data, 'prenom', '/^[\p{L}\s\-\']+$/u', 100);
        $email = InputValidator::requireEmail($data, 'email');
        $dateNaissance = InputValidator::requireDate($data, 'date_naissance');
        // Formation: academic text with simple punctuation.
        $formation = InputValidator::requireString($data, 'formation', '/^[\p{L}\p{N}\s\-\'".,()\/]+$/u', 150);
        // Description: free text but restricted to avoid unwanted characters.
        $description = InputValidator::requireString($data, 'description', '/^[\p{L}\p{N}\s\-\'".,()!?@:\/]*$/u', 2000, true);
        $role = InputValidator::getInt($data, 'role', 0, 0, 2);
        $estGerePar = InputValidator::getInt($data, 'est_gere_par', 0, 0);

        $stmt = $this->pdo->prepare("
            UPDATE Utilisateur SET
                Nom = :nom,
                Prenom = :prenom,
                Email = :email,
                Date_naissance = :date_naissance,
                Formation = :formation,
                Description = :description,
                est_gere_par = :est_gere_par,
                Role = :role
            WHERE Id_user = :id
        ");

        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':date_naissance' => $dateNaissance,
            ':formation' => $formation,
            ':description' => $description,
            ':est_gere_par' => $estGerePar > 0 ? $estGerePar : null,
            ':role' => $role,
            ':id' => $id,
        ]);
    }

    /**
     * Fetch candidatures for a given user.
     *
     * Normalizes each row to also contain an 'offre' nested object expected by some views.
     */
    public function getCandidatures(int $userId): array
    { 
        $sql = "
            SELECT c.*, o.Titre as offre_titre
            FROM Candidater c 
            LEFT JOIN Offre o ON c.Id_offre = o.Id_offre
            WHERE c.Id_user = :id_user
        ";
        
        $stmt = $this->pdo->prepare($sql); 
        $stmt->execute(['id_user' => $userId]);
        $stages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($stages as &$stage) {
            // Provide a normalized shape for templates that use stage.offre.titre.
            $stage['offre'] = [
                'titre' => $stage['offre_titre']
            ];
        }

        return $stages;
    }

    /**
     * Delete a user.
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Utilisateur WHERE Id_user = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
