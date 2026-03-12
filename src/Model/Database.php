<?php

class Database {
    private static PDO $pdo;

    public static function getConnection(): PDO {
        if (!isset(self::$pdo)) {
            self::$pdo = new PDO(
                "mysql:host=localhost;dbname=NOM_NOTRE_BDD;charset=utf8",               // IL FAUT QU'ON CHANGE LE NOM AVEC NOTRE VRAI NOM APRES     OU QU'ON VOIT POUR UNE AUTRE METHODE
                "root",                                                                 // IL FAUT QU'ON REMPLISSE AVEC LE BON NOM D'USER
                ""                                                                      // IL FAUT QU'ON REMPLISSE AVEC LE BON MDP
            );
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }
}

?>