<?php

namespace App\Controller;

use App\Core\View;
use App\Model\FicheModel;

class FicheController
{
    public function show()
    {
        $ficheModel = new FicheModel();

        // Accepte id ou id_user dans l'URL, avec une valeur de repli.
        $id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_GET['id_user']) ? (int) $_GET['id_user'] : 1);
        if ($id < 1) {
            $id = 1;
        }

        $elementsParPage = 10;
        
        // Lecture de l'url pour la page
        $pageActuelle = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($pageActuelle < 1) {
            $pageActuelle = 1;
        }

        //Calcul du nombre de page
        $totalCandidature = $ficheModel->getTotalCandidatures($id);
        $totalPages = max(1, (int) ceil($totalCandidature / $elementsParPage));


        $offset = ($pageActuelle - 1) * $elementsParPage;


        $candidatures = $ficheModel->getCandidaturesPaginated($id, $elementsParPage, $offset);
        
        $utilisateur = $ficheModel->getUserById($id);

        if(!$utilisateur) {
            header('Location : /');
            exit;
        }

   

        View::render('fiche_personne.html.twig', [
            'pageActuelle' => $pageActuelle,
            'totalPages' => $totalPages,
            'candidatures' => $candidatures,
            'userId' => $id,
            'utilisateur' => $utilisateur
        ]);
    }
}