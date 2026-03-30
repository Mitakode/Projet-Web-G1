<?php

namespace App\Model;

use PDO;

class PannelModel
{
    private $pdo;

    public function __construct() // à corriger pour la connexion à la base de données
    {

    $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",DB_USER,DB_PASS);
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }


    public function getTypeUser($id): int
    {
        $query = $this->pdo->prepare("SELECT Role FROM `Utilisateur` WHERE Id_user = ?");
        $query->execute([$id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return (int) $result['Role'];
    }

    public function getUserInfos(int $id): array
    {
        $userrequest = $this->pdo->prepare(
            "SELECT Id_user, Nom, Prenom, Email, Date_naissance, Formation, Description FROM Utilisateur WHERE Id_user = :id"
        );
        $userrequest->execute(['id' => $id]);
        $user = $userrequest->fetch(PDO::FETCH_ASSOC) ?: [];

        $statsrequest = $this->pdo->prepare(
            "SELECT COUNT(*) AS total FROM Candidater WHERE Id_user = :id"
        );
        $statsrequest->execute(['id' => $id]);
        $totalCandidatures = (int) $statsrequest->fetch(PDO::FETCH_ASSOC)['total'];

        $cvrequest = $this->pdo->prepare(
            "SELECT Cv FROM Candidater WHERE Id_user = :id AND Cv IS NOT NULL AND Cv <> '' ORDER BY Date_ DESC LIMIT 1"
        );
        $cvrequest->execute(['id' => $id]);
        $cv = $cvrequest->fetchColumn() ?: null;

        $competencesrequest = $this->pdo->prepare(
            "SELECT DISTINCT c.Nom
             FROM Competence c
             JOIN Requiert r ON r.Id_competence = c.Id_competence
             JOIN Candidater ca ON ca.Id_offre = r.Id_offre
             WHERE ca.Id_user = :id
             ORDER BY c.Nom ASC
             LIMIT 2"
        );
        $competencesrequest->execute(['id' => $id]);
        $competences = array_column($competencesrequest->fetchAll(PDO::FETCH_ASSOC), 'Nom');

        $favorisrequest = $this->pdo->prepare(
            "SELECT o.Id_offre, o.Titre, o.Description, o.Date_offre, e.Nom AS entreprise
             FROM Wishlist w
             JOIN Offre o ON o.Id_offre = w.Id_offre
             JOIN Entreprise e ON e.Id_entreprise = o.Id_entreprise
             WHERE w.Id_user = :id
             ORDER BY o.Date_offre DESC"
        );
        $favorisrequest->execute(['id' => $id]);
        $favoris = $favorisrequest->fetchAll(PDO::FETCH_ASSOC);

        $candidaturesrequest = $this->pdo->prepare(
            "SELECT o.Id_offre,
                    o.Id_entreprise,
                    o.Titre,
                    o.Description,
                    o.Date_offre,
                    e.Nom AS entreprise,
                    ca.Date_ AS date_candidature,
                    ca.Cv,
                    ca.LM,
                    COALESCE(notesAgg.note_moyenne, 0) AS note_moyenne,
                    COALESCE(notesAgg.total_votes, 0) AS total_votes,
                    noteUser.Note AS note_utilisateur
             FROM Candidature ca
             JOIN Offre o ON o.Id_offre = ca.Id_offre
             JOIN Entreprise e ON e.Id_entreprise = o.Id_entreprise
                 LEFT JOIN (
                     SELECT Id_Entreprise AS Id_entreprise, ROUND(AVG(Note), 2) AS note_moyenne, COUNT(*) AS total_votes
                     FROM Evaluer
                     GROUP BY Id_Entreprise
                 ) notesAgg ON notesAgg.Id_entreprise = o.Id_entreprise
                 LEFT JOIN Evaluer noteUser
                          ON noteUser.Id_Entreprise = o.Id_entreprise
                         AND noteUser.Id_User = ca.Id_user
             WHERE ca.Id_user = :id
             ORDER BY ca.Date_ DESC
             LIMIT 5"
        );
        $candidaturesrequest->execute(['id' => $id]);
        $dernieresCandidatures = $candidaturesrequest->fetchAll(PDO::FETCH_ASSOC);

        return [
            'user' => $user,
            'stats' => [
                'total' => $totalCandidatures,
                // A implémenter
                'en_cours' => $totalCandidatures,
                'refusees' => 0,
            ],
            'profil' => [
                'date_naissance' => $user['Date_naissance'] ?? '',
                'formation' => $user['Formation'] ?? '',
                'niveau_etude' => $user['Niveau_etude'] ?? ($user['Formation'] ?? ''),
                'description' => $user['Description'] ?? '',
            ],
            'cv' => $cv,
            'competences' => $competences,
            'favoris' => $favoris,
            'dernieres_candidatures' => $dernieresCandidatures,
        ];
    }
}
