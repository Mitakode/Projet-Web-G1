<?php

namespace App\Controller;

use App\Core\Auth;
use App\Core\View;
use App\Model\PannelModel;
use App\Model\UtilisateurModel;
use App\Model\EntrepriseModel;
use App\Model\OffreModel;
use App\Model\StatsModel;

class AccountController
{
    public function index()
    {
        Auth::requireAuth();

        $user = Auth::user();
        $id   = $user['Id_user'];       
        $role = $user['Role'];          
 
        switch ($role) {
            case 0: // Eleve normal
                $pannelModel = new PannelModel();

                $infos = $pannelModel->getUserInfos($id);
                View::render('pannel_eleve.html.twig', [
                    'infos' => $infos,
                ]);
                return;

            case 1: // Pilote
                $utilisateurModel = new UtilisateurModel();
                $entrepriseModel  = new EntrepriseModel();
                $offreModel       = new OffreModel();

                View::render('pannel_pilote.html.twig', [
                    'user'        => $user,
                    'eleves'      => $utilisateurModel->getByRoleAndEstGerePar(0,$id),
                    'offres'      => $offreModel->getAll(),
                    'entreprises' => $entrepriseModel->getAll(),
                ]);
                return;

            case 2: // Admin
                $utilisateurModel = new UtilisateurModel();
                $entrepriseModel  = new EntrepriseModel();
                $offreModel       = new OffreModel();
                $statsModel       = new StatsModel();
                $statsOffres = [
                    'total_offres'      => $statsModel->getTotalOffres(),
                    'total_candidatures'=> $statsModel->getTotalCandidatures(),
                    'avg_candidatures'  => $statsModel->getAverageCandidaturesPerOffre(),
                    'duree_repartition' => $statsModel->getOffreDurationBreakdown(),
                    'top_wishlist'      => $statsModel->getTopWishlistOffres(),
                    'top_candidatures'  => $statsModel->getTopCandidatureOffres(),
                ];
                $statsEntreprises = [
                    'total_entreprises' => $statsModel->getTotalEntreprises(),
                    'avg_offres'        => $statsModel->getAverageOffresPerEntreprise(),
                    'top_offres'        => $statsModel->getTopEntreprisesByOffres(),
                    'top_candidatures'  => $statsModel->getTopEntreprisesByCandidatures(),
                ];
                $statsEleves = [
                    'total_eleves'      => $statsModel->getTotalEleves(),
                    'avg_candidatures'  => $statsModel->getAverageCandidaturesPerEleve(),
                    'top_candidatures'  => $statsModel->getTopElevesByCandidatures(),
                    'top_wishlist'      => $statsModel->getTopElevesByWishlist(),
                ];

                View::render('pannel_admin.html.twig', [
                    'user'        => $user,
                    'pilotes'     => $utilisateurModel->getByRole(1),
                    'eleves'      => $utilisateurModel->getByRole(0),
                    'entreprises' => $entrepriseModel->getAll(),
                    'offres'      => $offreModel->getAll(),
                    'statsOffres' => $statsOffres,
                    'statsEntreprises' => $statsEntreprises,
                    'statsEleves' => $statsEleves,
                ]);
                return;

        }

        
    }
}
