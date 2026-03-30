<?php

namespace App\Model;

use PDO;

class StatsModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
            DB_USER,
            DB_PASS
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getTotalOffres(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM Offre");
        return (int) $stmt->fetchColumn();
    }

    public function getTotalCandidatures(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM Candidater");
        return (int) $stmt->fetchColumn();
    }

    public function getTotalEntreprises(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM Entreprise");
        return (int) $stmt->fetchColumn();
    }

    public function getTotalEleves(): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM Utilisateur WHERE Role = :role");
        $stmt->execute(['role' => 0]);
        return (int) $stmt->fetchColumn();
    }

    public function getAverageOffresPerEntreprise(): float
    {
        $totalEntreprises = $this->getTotalEntreprises();
        if ($totalEntreprises === 0) {
            return 0.0;
        }

        $totalOffres = $this->getTotalOffres();
        return $totalOffres / $totalEntreprises;
    }

    public function getAverageCandidaturesPerEleve(): float
    {
        $totalEleves = $this->getTotalEleves();
        if ($totalEleves === 0) {
            return 0.0;
        }

        $totalCandidatures = $this->getTotalCandidatures();
        return $totalCandidatures / $totalEleves;
    }

    public function getAverageCandidaturesPerOffre(): float
    {
        $totalOffres = $this->getTotalOffres();
        if ($totalOffres === 0) {
            return 0.0;
        }

        $totalCandidatures = $this->getTotalCandidatures();
        return $totalCandidatures / $totalOffres;
    }

    public function getOffreDurationBreakdown(): array
    {
        $stmt = $this->pdo->query("
            SELECT Duree_mois AS duree_mois, COUNT(*) AS total
            FROM Offre
            GROUP BY Duree_mois
            ORDER BY Duree_mois ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopWishlistOffres(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare("
            SELECT o.Id_offre, o.Titre, e.Nom AS Nom_entreprise, COUNT(w.Id_offre) AS total_wishlist
            FROM Offre o
            LEFT JOIN Wishlist w ON w.Id_offre = o.Id_offre
            LEFT JOIN Entreprise e ON e.Id_entreprise = o.Id_entreprise
            GROUP BY o.Id_offre
            ORDER BY total_wishlist DESC, o.Id_offre DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopCandidatureOffres(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare("
            SELECT o.Id_offre, o.Titre, e.Nom AS Nom_entreprise, COUNT(ca.Id_offre) AS total_candidatures
            FROM Offre o
            LEFT JOIN Candidater ca ON ca.Id_offre = o.Id_offre
            LEFT JOIN Entreprise e ON e.Id_entreprise = o.Id_entreprise
            GROUP BY o.Id_offre
            ORDER BY total_candidatures DESC, o.Id_offre DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopEntreprisesByOffres(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare("
            SELECT e.Id_entreprise, e.Nom, COUNT(o.Id_offre) AS total_offres
            FROM Entreprise e
            LEFT JOIN Offre o ON o.Id_entreprise = e.Id_entreprise
            GROUP BY e.Id_entreprise
            ORDER BY total_offres DESC, e.Id_entreprise DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopEntreprisesByCandidatures(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare("
            SELECT e.Id_entreprise, e.Nom, COUNT(ca.Id_offre) AS total_candidatures
            FROM Entreprise e
            LEFT JOIN Offre o ON o.Id_entreprise = e.Id_entreprise
            LEFT JOIN Candidater ca ON ca.Id_offre = o.Id_offre
            GROUP BY e.Id_entreprise
            ORDER BY total_candidatures DESC, e.Id_entreprise DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopElevesByCandidatures(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare("
            SELECT u.Id_user, u.Nom, u.Prenom, COUNT(ca.Id_offre) AS total_candidatures
            FROM Utilisateur u
            LEFT JOIN Candidater ca ON ca.Id_user = u.Id_user
            WHERE u.Role = 0
            GROUP BY u.Id_user
            ORDER BY total_candidatures DESC, u.Id_user DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopElevesByWishlist(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare("
            SELECT u.Id_user, u.Nom, u.Prenom, COUNT(w.Id_offre) AS total_wishlist
            FROM Utilisateur u
            LEFT JOIN Wishlist w ON w.Id_user = u.Id_user
            WHERE u.Role = 0
            GROUP BY u.Id_user
            ORDER BY total_wishlist DESC, u.Id_user DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOfferStats(int $id): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                o.Id_offre,
                o.Titre,
                o.Description,
                o.Date_offre,
                o.Duree_mois,
                o.Nombre_place,
                e.Nom AS Nom_entreprise,
                COUNT(DISTINCT w.Id_user) AS total_wishlist,
                COUNT(DISTINCT ca.Id_user) AS total_candidatures
            FROM Offre o
            LEFT JOIN Entreprise e ON e.Id_entreprise = o.Id_entreprise
            LEFT JOIN Wishlist w ON w.Id_offre = o.Id_offre
            LEFT JOIN Candidater ca ON ca.Id_offre = o.Id_offre
            WHERE o.Id_offre = :id
            GROUP BY o.Id_offre
            LIMIT 1
        ");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
