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
        $sql = "INSERT INTO Candidater (Id_offre, Id_user, Cv, LM, Date_) VALUES (:idOffre, :idUser, :cv, :lm, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':idOffre' => $idOffre,
            ':idUser' => $idUser,
            ':cv' => $cvName,
            ':lm' => $lmName
        ]);
        return $this->pdo->lastInsertId();
    }

    public function candidatureExists($idOffre, $idUser)
    {
        $sql = "SELECT 1 FROM Candidater WHERE Id_offre = :idOffre AND Id_user = :idUser LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':idOffre' => $idOffre,
            ':idUser' => $idUser,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function getCandidatureDocumentName(int $idOffre, int $idUser, string $type): ?string
    {
        if (!isset($this->pdo)) {
            return null;
        }

        $column = ($type === 'lm') ? 'LM' : 'Cv';
        $sql = "SELECT {$column} FROM Candidater WHERE Id_offre = :idOffre AND Id_user = :idUser LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':idOffre' => $idOffre,
            ':idUser' => $idUser,
        ]);

        $name = $stmt->fetchColumn();
        if (!is_string($name) || $name === '') {
            return null;
        }

        return $name;
    }
}
