<?php

namespace App\Controller;

use App\Core\View;
use App\Model\OffreModel;

class AccountController
{
    public function index()
    {

        $offreModel = new OffreModel(); // On initialise le modèle


        $elementsParPage = 10;

        // Lecture de l'url pour la page
        $pageActuelle = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($pageActuelle < 1) {
            $pageActuelle = 1;
        }

        // Calcul du nombre de page
        $totalElements = $offreModel->getTotalOffres();
        $totalPages = ceil($totalElements / $elementsParPage);

        // Calcul du démarrage
        $offset = ($pageActuelle - 1) * $elementsParPage;

        // On demande au modèle de nous envoyer les pages de x à y
        $offres = $offreModel->getOffresPaginated($elementsParPage, $offset);

        View::render('home.html.twig', [
            'offres' => $offres,
            'totalPages' => $totalPages,
            'pageActuelle' => $pageActuelle
        ]);
    }
}