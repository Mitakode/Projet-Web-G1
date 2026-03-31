<?php

namespace App\Model;

use App\Core\InputValidator;
use InvalidArgumentException;
use PDO;

/**
 * Authentication data access.
 *
 * Provides user lookup/creation and password update operations.
 */
class AuthModel
{
    private $pdo;

    public function __construct()
    {
        // Create a PDO connection using config constants (see config/config.php).
        $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Fetch a user record by email.
     *
     * Returns false for invalid emails or when no user is found.
     */
    public function getByEmail(string $email): array|false
    {
        // Validate email format before running the query.
        if (!InputValidator::regex($email, '/^[^\s@]{1,64}@[A-Za-z0-9.-]{1,190}\.[A-Za-z]{2,63}$/')) {
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT * FROM Utilisateur WHERE Email = :email LIMIT 1");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check whether an account with the given email already exists.
     */
    public function emailExists(string $email): bool
    {
        // Validate email format before existence check.
        if (!InputValidator::regex($email, '/^[^\s@]{1,64}@[A-Za-z0-9.-]{1,190}\.[A-Za-z]{2,63}$/')) {
            return false;
        }

        $stmt = $this->pdo->prepare("SELECT 1 FROM Utilisateur WHERE Email = :email LIMIT 1");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Create a new user.
     *
     * Expects a pre-hashed password in $data['Password'].
     *
     * @throws InvalidArgumentException When the email is missing/invalid.
     */
    public function createUser(array $data): array|false
    {
        // Validate email before inserting.
        if (!isset($data['Email']) || !InputValidator::regex((string) $data['Email'], '/^[^\s@]{1,64}@[A-Za-z0-9.-]{1,190}\.[A-Za-z]{2,63}$/')) {
            throw new InvalidArgumentException('Email invalide.');
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO Utilisateur (Nom, Prenom, Date_naissance, Formation, Description, Email, Password, Role, est_gere_par)
             VALUES (:nom, :prenom, :date_naissance, :formation, :description, :email, :password, :role, :est_gere_par)"
        );

        $ok = $stmt->execute([
            'nom' => $data['Nom'],
            'prenom' => $data['Prenom'],
            'date_naissance' => $data['Date_naissance'],
            'formation' => $data['Formation'],
            'description' => $data['Description'],
            'email' => $data['Email'],
            'password' => $data['Password'],
            'role' => $data['Role'],
            'est_gere_par' => $data['est_gere_par'],
        ]);

        if (!$ok) {
            return false;
        }

        // Return the full record as stored in DB.
        return $this->getByEmail($data['Email']);
    }

    /**
     * Update the stored password hash for a user.
     */
    public function updatePassword(int $id, string $hash): bool
    {
        $stmt = $this->pdo->prepare("UPDATE Utilisateur SET Password = :hash WHERE Id_user = :id");
        return $stmt->execute([
            'hash' => $hash,
            'id' => $id,
        ]);
    }
}
