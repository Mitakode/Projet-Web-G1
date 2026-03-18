<?php

namespace App\Model;

use PDO;

class PannelModel
{
    private $pdo;

    public function __construct() // à corriger pour la connexion à la base de données
    {

        $host = 'db';
        $port = 3306;
        $dbname = 'projet_db';
        $user = 'projet_user';
        $pass = 'projet_pass';
        
        $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }


    public function getTypeUser($id)
    {
        $query = $this->pdo->prepare("SELECT Rôle FROM `Utilisateur` WHERE Id_user = ?");
        $query->execute([$id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return (int) $result['Rôle'];
    }

    public function getUserInfos




}