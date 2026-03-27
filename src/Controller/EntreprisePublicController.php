<?php

namespace App\Controller;

use App\Model\EntrepriseModel;
use App\Core\View;

class EntreprisePublicController
{
    public function index()
    {
        $model = new EntrepriseModel();
        $search = $_GET['search'] ?? '';
        $entreprises = $search ? $model->search($search) : $model->getAll();

        View::render('companies.html.twig', [
            'entreprises' => $entreprises,
            'search'      => $search
        ]);
    }

    public function fiche()
    {
        $model = new EntrepriseModel();
        $id = (int)($_GET['id'] ?? 0);

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