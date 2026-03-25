<?php

namespace App\Model;

use PDO;

class WishlistModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function exists(int $userId, int $offreId): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM Wishlist WHERE Id_user = :user_id AND Id_offre = :offre_id LIMIT 1"
        );
        $stmt->execute([
            'user_id' => $userId,
            'offre_id' => $offreId,
        ]);
        return (bool) $stmt->fetchColumn();
    }

    public function add(int $userId, int $offreId): bool
    {
        if ($this->exists($userId, $offreId)) {
            return true;
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO Wishlist (Id_user, Id_offre) VALUES (:user_id, :offre_id)"
        );
        return $stmt->execute([
            'user_id' => $userId,
            'offre_id' => $offreId,
        ]);
    }

    public function remove(int $userId, int $offreId): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM Wishlist WHERE Id_user = :user_id AND Id_offre = :offre_id"
        );
        return $stmt->execute([
            'user_id' => $userId,
            'offre_id' => $offreId,
        ]);
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT o.Id_offre, o.Titre, o.Description, o.Date_offre, e.Nom AS entreprise
             FROM Wishlist w
             JOIN Offre o ON o.Id_offre = w.Id_offre
             JOIN Entreprise e ON e.Id_entreprise = o.Id_entreprise
             WHERE w.Id_user = :user_id
             ORDER BY o.Date_offre DESC"
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
