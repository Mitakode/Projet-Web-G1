<?php

namespace App\Controller;

use App\Core\View;
use App\Model\OffreModel;

class HomeController
{
    public function index()
    {
        $offreModel = new OffreModel();
        $searchQuery = isset($_GET['query']) ? trim((string) $_GET['query']) : '';

        $elementsParPage = 10;

        $pageActuelle = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($pageActuelle < 1) {
            $pageActuelle = 1;
        }

        $totalElements = $offreModel->getTotalOffres($searchQuery);
        $totalPages = ceil($totalElements / $elementsParPage);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        if ($pageActuelle > $totalPages) {
            $pageActuelle = $totalPages;
        }

        $offset = ($pageActuelle - 1) * $elementsParPage;

        $offres = $offreModel->getOffresPaginated($elementsParPage, $offset, $searchQuery);

        View::render('home.html.twig', [
            'offres'      => $offres,
            'totalPages'  => $totalPages,
            'pageActuelle' => $pageActuelle,
            'searchQuery' => $searchQuery
        ]);
    }
}
