<?php

require_once 'Database.php';

class HomeModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection(); 
    }


public function getOffres1() {            // RéRecupere toutes les offres actives avec le nom de l'entreprise. Pour les cartes a droite de l'ecran.
    $stmt = $this->pdo->prepare("
        SELECT o.Id_offre, o.Titre, o.Remuneration, e.Nom AS Entreprise_Nom, e.Description AS Entreprise_Description
        FROM Offre o
        JOIN Entreprise e ON o.Id_entreprise = e.Id_entreprise
        WHERE e.Est_actif = TRUE
        ORDER BY o.Date_offre DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


public function getOffreById(int $id) {   // Récupère une offre précise quand l'utilisateur clique sur une carte
    $stmt = $this->pdo->prepare("
        SELECT o.*, e.Nom AS Entreprise_Nom, e.Email_contact
        FROM Offre o
        JOIN Entreprise e ON o.Id_entreprise = e.Id_entreprise
        WHERE o.Id_offre = :id
    ");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
    }



public function searchOffres(string $search) {  // Nom de l'entreprise pour la barre de recherche
    $terme = '%' . $search . '%';
    $stmt = $this->pdo->prepare("
        SELECT o.Id_offre, o.Titre, o.Description, o.Remuneration,
               e.Nom AS Entreprise_Nom
        FROM Offre o
        JOIN Entreprise e ON o.Id_entreprise = e.Id_entreprise
        WHERE o.Titre LIKE :terme 
           OR o.Description LIKE :terme
           OR e.Nom LIKE :terme
        ORDER BY o.Date_offre DESC
    ");
    $stmt->execute([':terme' => $terme]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}  ?>