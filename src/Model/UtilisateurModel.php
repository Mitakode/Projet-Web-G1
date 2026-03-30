<?php

namespace App\Model;

use App\Core\InputValidator;
use InvalidArgumentException;
use PDO;

class UtilisateurModel
{
    private $pdo;

    public function __construct()
    {
    $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",DB_USER,DB_PASS);
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getByRoleAndEstGerePar(int $role, int $est_gere_par): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Utilisateur WHERE `Role` = :role AND est_gere_par = :est_gere_par");
        $stmt->bindValue(':role', $role, PDO::PARAM_INT);
        $stmt->bindValue(':est_gere_par', $est_gere_par, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByRole(int $role): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Utilisateur WHERE Role = :role");
        $stmt->bindValue(':role', $role, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Utilisateur WHERE Id_user = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM Utilisateur ORDER BY Id_user ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): void
    {
        // Nom/prenom: lettres, espaces, tirets et apostrophes.
        $nom = InputValidator::requireString($data, 'nom', '/^[\p{L}\s\-\']+$/u', 100);
        $prenom = InputValidator::requireString($data, 'prenom', '/^[\p{L}\s\-\']+$/u', 100);
        $dateNaissance = InputValidator::requireDate($data, 'date_naissance');
        // Formation: texte académique avec ponctuation simple.
        $formation = InputValidator::requireString($data, 'formation', '/^[\p{L}\p{N}\s\-\'".,()\/]+$/u', 150);
        // Description: texte libre encadré pour éviter les caractères indésirables.
        $description = InputValidator::requireString($data, 'description', '/^[\p{L}\p{N}\s\-\'".,()!?@:\/]*$/u', 2000, true);
        $email = InputValidator::requireEmail($data, 'email');
        // Mot de passe: 8 à 255 caractères, sans caractère nul binaire.
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
            'password'       => password_hash($passwordRaw, PASSWORD_DEFAULT),
            'role'           => $role,
            'est_gere_par'   => $estGerePar > 0 ? $estGerePar : null,
        ]);
    }

    public function update(int $id, array $data): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID utilisateur invalide.');
        }

        // Nom/prenom: lettres, espaces, tirets et apostrophes.
        $nom = InputValidator::requireString($data, 'nom', '/^[\p{L}\s\-\']+$/u', 100);
        $prenom = InputValidator::requireString($data, 'prenom', '/^[\p{L}\s\-\']+$/u', 100);
        $email = InputValidator::requireEmail($data, 'email');
        $dateNaissance = InputValidator::requireDate($data, 'date_naissance');
        // Formation: texte académique avec ponctuation simple.
        $formation = InputValidator::requireString($data, 'formation', '/^[\p{L}\p{N}\s\-\'".,()\/]+$/u', 150);
        // Description: texte libre encadré pour éviter les caractères indésirables.
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

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Utilisateur WHERE Id_user = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
