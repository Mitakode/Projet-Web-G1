<?php

namespace App\Model;

use PDO;

class EntrepriseModel
{
    private $pdo;

    public function __construct()
    {
        $host   = 'db';
        $dbname = 'projet_db';
        $user   = 'projet_user';
        $pass   = 'projet_pass';

        $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAll(): array
    {
        $query = $this->pdo->query("SELECT * FROM Entreprise");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
