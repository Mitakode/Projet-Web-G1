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

        View::render('companies.html.twig', [
            'entreprises' => $entreprises,
            'search'      => $search
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