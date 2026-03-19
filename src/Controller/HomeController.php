<?php

namespace App\Controller;

use App\Core\View;
use App\Model\OffreModel;

class HomeController
{
    public function index()
    {
        $offreModel = new OffreModel();

        $elementsParPage = 10;

        $pageActuelle = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($pageActuelle < 1) {
            $pageActuelle = 1;
        }

        $totalElements = $offreModel->getTotalOffres();
        $totalPages = ceil($totalElements / $elementsParPage);

        $offset = ($pageActuelle - 1) * $elementsParPage;

        $offres = $offreModel->getOffresPaginated($elementsParPage, $offset);

        View::render('home.html.twig', [
            'offres'      => $offres,
            'totalPages'  => $totalPages,
            'pageActuelle' => $pageActuelle
        ]);
    }
}
