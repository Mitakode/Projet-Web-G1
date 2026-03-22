<?php

namespace App\Model;

use PDO;

class CandidatureModel
{
    private $pdo;

    public function __construct()
    {
        if (defined('DB_HOST')) {
            $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    public function createCandidature($idOffre, $idUser, $cvName, $lmName)
    {
<<<<<<< HEAD
        $sql = "INSERT INTO Candidater (Id_offre, Id_user, Cv, LM, Date_) VALUES (:idOffre, :idUser, :cv, :lm, NOW())";
=======
        $sql = "INSERT INTO Candidature (Id_offre, Id_user, Cv, Lettre_motivation, Date_) VALUES (:idOffre, :idUser, :cv, :lm, NOW())";
>>>>>>> a60df6e (Ajout v1 page candidature, modif sur offreModel pour getOffre, manque le submit)
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':idOffre' => $idOffre,
            ':idUser' => $idUser,
            ':cv' => $cvName,
            ':lm' => $lmName
        ]);
        return $this->pdo->lastInsertId();
    }
}
