<?php

namespace App\Model;

use PDO;

class Pannel_eleve
{
    private $pdo;

    public function __construct()
    {
        
        $host = 'db';
        $port = 3306;
        $dbname = 'projet_db';
        $user = 'projet_user';
        $pass = 'projet_pass';
        
        $this->pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }


    public function getTypeUser()
    {
        $query = $this->pdo->query("SELECT COUNT(*) as total FROM Offre");
        $result = $query->fetch(PDO::FETCH_ASSOC);
        return (int) $result['total'];
    }