<?php

namespace App\Model;

use PDO;

class EntrepriseModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAll(): array
    {
        $query = $this->pdo->query("SELECT * FROM Entreprise");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    private function hasAppliedInTable(string $tableName, int $idUser, int $idEntreprise): bool
    {
        // Vérifie l'existence d'au moins une candidature de l'utilisateur
        // vers une offre appartenant à l'entreprise ciblée.
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

    public function canUserRateEntreprise(int $idUser, int $idEntreprise): bool
    {
        // Le projet utilise la table Candidater pour les candidatures.
        return $this->hasAppliedInTable('Candidater', $idUser, $idEntreprise);
    }

    public function noterEntreprise(int $idUser, int $idEntreprise, int $note): bool
    {
        if ($note < 1 || $note > 5) {
            return false;
        }

        // UPSERT: si la note existe déjà pour (user, entreprise), elle est remplacée.
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

    public function getNoteMoyenneEntreprise(int $idEntreprise): array
    {
        // Renvoie moyenne arrondie + nombre total d'avis pour l'entreprise.
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

    public function getUserNoteEntreprise(int $idUser, int $idEntreprise): ?int
    {
        // Sert à pré-remplir la note dans l'interface si l'utilisateur a déjà voté.
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


    public function getById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Entreprise WHERE Id_entreprise = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO Entreprise (Nom, Description, Email_contact, Telephone, Est_actif)
            VALUES (:nom, :description, :email_contact, :telephone, :est_actif)
        ");
        $stmt->execute([
            ':nom'          => $data['nom'],
            ':description'  => $data['description'],
            ':email_contact'=> $data['email_contact'],
            ':telephone'    => $data['telephone'] ?: null,
            ':est_actif'    => $data['est_actif'] ?? 1,
        ]);
    }

    public function update(int $id, array $data): void
    {
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
            ':nom'          => $data['nom'],
            ':description'  => $data['description'],
            ':email_contact'=> $data['email_contact'],
            ':telephone'    => $data['telephone'] ?: null,
            ':est_actif'    => $data['est_actif'] ?? 1,
            ':id'           => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Entreprise WHERE Id_entreprise = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function search(string $search): array
{
    $stmt = $this->pdo->prepare("
        SELECT * FROM Entreprise 
        WHERE Nom LIKE :s 
        OR Description LIKE :s 
        OR Email_contact LIKE :s
    ");
    $stmt->execute([':s' => '%' . $search . '%']);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getOffresByEntreprise(int $id): array
{
    $stmt = $this->pdo->prepare("
        SELECT * FROM Offre WHERE Id_entreprise = :id
    ");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getStats(int $id): array
{
    $stmt = $this->pdo->prepare("
        SELECT COUNT(*) as nb_candidatures
        FROM Candidater c
        JOIN Offre o ON c.Id_offre = o.Id_offre
        WHERE o.Id_entreprise = :id
    ");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $nb = $stmt->fetch(PDO::FETCH_ASSOC);

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
    private function hasAppliedInTable(string $tableName, int $idUser, int $idEntreprise): bool
    {
        // Vérifie l'existence d'au moins une candidature de l'utilisateur
        // vers une offre appartenant à l'entreprise ciblée.
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

    public function canUserRateEntreprise(int $idUser, int $idEntreprise): bool
    {
        // Le projet utilise la table Candidater pour les candidatures.
        return $this->hasAppliedInTable('Candidater', $idUser, $idEntreprise);
    }

    public function noterEntreprise(int $idUser, int $idEntreprise, int $note): bool
    {
        if ($note < 1 || $note > 5) {
            return false;
        }

        // UPSERT: si la note existe déjà pour (user, entreprise), elle est remplacée.
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

    public function getNoteMoyenneEntreprise(int $idEntreprise): array
    {
        // Renvoie moyenne arrondie + nombre total d'avis pour l'entreprise.
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

    public function getUserNoteEntreprise(int $idUser, int $idEntreprise): ?int
    {
        // Sert à pré-remplir la note dans l'interface si l'utilisateur a déjà voté.
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


}
