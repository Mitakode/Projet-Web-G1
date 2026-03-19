<?php

namespace App\Controller;

use App\Core\View;
use App\Model\PannelModel;
use App\Model\UtilisateurModel;
use App\Model\EntrepriseModel;
use App\Model\OffreModel;

class AccountController
{
    public function index()
    {
        $user = $_SESSION['user'];      
        $id   = $user['Id_user'];       
        $role = $user['Rôle'];          

        switch ($role) {
            case 1: // Eleve normal
                $pannelModel = new PannelModel();           // On initialise le modèle

                $infos = $pannelModel->getUserInfos($id);
                View::render('pannel_eleve.html.twig', [
                    'infos' => $infos,
                ]);
                return;

            case 2: //Pilote
                    
                break;

            case 3: // Admin
                $utilisateurModel = new UtilisateurModel();
                $entrepriseModel  = new EntrepriseModel();
                $offreModel       = new OffreModel();

                View::render('pannel_admin.html.twig', [
                    'user'        => $user,
                    'pilotes'     => $utilisateurModel->getByRole(2),
                    'eleves'      => $utilisateurModel->getByRole(1),
                    'entreprises' => $entrepriseModel->getAll(),
                    'offres'      => $offreModel->getAll(),
                ]);
                return;

        }

        
    }
}

