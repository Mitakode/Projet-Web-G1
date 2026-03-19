<?php

namespace App\Controller;

use App\Core\View;
use App\Model\OffreModel;
use App\Model\PannelModel;

class AccountController
{
    public function index()
    {

        $pannelModel = new PannelModel(); // On initialise le modèle
        $role = $pannelModel->getTypeUser($id); // On récupère le rôle de l'utilisateur connecté
    


        switch ($role) {
            case 1: // Eleve normal
                $infos = $pannelModel->getUserInfos($id);
                View::render('pannel_eleve.html.twig', [
                    'infos' => $infos,
                ]);
                return;

            case 2:
                    //Pilote
                break;

            case 3:
                    //Admin
                break;

        }

        
    }
}

