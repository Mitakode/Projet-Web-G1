<?php

namespace App\Controller;

use App\Core\Auth;
use App\Core\View;
use App\Model\PannelModel;
use App\Model\UtilisateurModel;
use App\Model\EntrepriseModel;
use App\Model\OffreModel;

class AccountController
{
    public function index()
    {
        Auth::requireAuth();

        $user = Auth::user();
        $id   = $user['Id_user'];       
        $role = $user['Role'];          

        switch ($role) {
            case 1: // Eleve normal
                $pannelModel = new PannelModel();

                $infos = $pannelModel->getUserInfos($id);
                View::render('pannel_eleve.html.twig', [
                    'infos' => $infos,
                ]);
                return;

            case 2: // Pilote
                $utilisateurModel = new UtilisateurModel();
                $entrepriseModel  = new EntrepriseModel();
                $offreModel       = new OffreModel();

                View::render('pannel_pilote.html.twig', [
                    'user'        => $user,
                    'eleves'      => $utilisateurModel->getByRole(1),
                    'offres'      => $offreModel->getAll(),
                    'entreprises' => $entrepriseModel->getAll(),
                ]);
                return;

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
