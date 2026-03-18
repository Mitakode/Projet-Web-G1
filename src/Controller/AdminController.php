<?php

namespace App\Controller;

use App\Core\View;
use App\Model\UtilisateurModel;
use App\Model\EntrepriseModel;
use App\Model\OffreModel;

class AdminController
{
    public function index(): void
    {
        $utilisateurModel = new UtilisateurModel();
        $entrepriseModel  = new EntrepriseModel();
        $offreModel       = new OffreModel();

        $user        = $_SESSION['user'] ?? null;
        $pilotes     = $utilisateurModel->getByRole(2);
        $eleves      = $utilisateurModel->getByRole(3);
        $entreprises = $entrepriseModel->getAll();
        $offres      = $offreModel->getAll();

        View::render('pannel_admin.html.twig', [
            'user'        => $user,
            'pilotes'     => $pilotes,
            'eleves'      => $eleves,
            'entreprises' => $entreprises,
            'offres'      => $offres,
        ]);
    }
}