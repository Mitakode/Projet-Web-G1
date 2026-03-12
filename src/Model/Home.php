<?php

require_once 'Database.php';

class HomeModel {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection(); 
    }


public function getOffres() {         // RéRecupere toutes les offres actives avec le nom de l'entreprise. Pour les cartes a droite de l'ecran.
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

}  ?>