<?php

namespace App\Controller;

use App\Core\View;
use App\Model\OffreModel;
use App\Model\CandidatureModel;

class CandidatureController
{
    public function index()
    {
        // Récupérer l'ID de l'offre depuis l'URL
        $id = isset($_GET['id_offre']) ? (int)$_GET['id_offre'] : 0;
        
        $offreModel = new OffreModel();
        $offre = $offreModel->getOffreById($id);

        // Si l'offre n'existe pas, redirection vers l'accueil
        if (!$offre) {
            header('Location: /');
            exit;
        }

        View::render('candidature.html.twig', [
            'offre' => $offre
        ]);
    }

    public function submit()
    {
   
    }
}
