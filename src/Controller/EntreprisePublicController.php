<?php

namespace App\Controller;

use App\Core\InputValidator;
use App\Model\EntrepriseModel;
use App\Core\View;

class EntreprisePublicController
{
    public function index()
    {
        $model = new EntrepriseModel();
        $search = trim((string) ($_GET['search'] ?? ''));
        // Recherche entreprise: même jeu de caractères autorisés que l'accueil.
        if (!InputValidator::regex($search, '/^[\p{L}\p{N}\s\-\'".,()@]{0,100}$/u')) {
            $search = '';
        }
        $entreprises = $search ? $model->search($search) : $model->getAll();

        $elementsParPage = 20;
        $pageActuelle = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($pageActuelle < 1) {
            $pageActuelle = 1;
        }

        $totalElements = count($entreprises);
        $totalPages = (int) ceil($totalElements / $elementsParPage);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        if ($pageActuelle > $totalPages) {
            $pageActuelle = $totalPages;
        }

        $offset = ($pageActuelle - 1) * $elementsParPage;
        $entreprisesPage = array_slice($entreprises, $offset, $elementsParPage);

        View::render('companies.html.twig', [
            'entreprises'   => $entreprisesPage,
            'search'        => $search,
            'pageActuelle'  => $pageActuelle,
            'totalPages'    => $totalPages,
            'totalElements' => $totalElements,
        ]);
    }

    public function fiche()
    {
        $model = new EntrepriseModel();
        $id = InputValidator::getInt($_GET, 'id', 0, 1);

        $entreprise = $model->getById($id);
        $offres     = $model->getOffresByEntreprise($id);
        $stats      = $model->getStats($id);

        View::render('company.html.twig', [
            'entreprise' => $entreprise,
            'offres'     => $offres,
            'stats'      => $stats
        ]);
    }
}