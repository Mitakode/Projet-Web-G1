<?php

namespace App\Controller;

use App\Core\Auth;
use App\Core\InputValidator;
use App\Core\View;
use App\Model\PannelModel;
use App\Model\UtilisateurModel;
use App\Model\EntrepriseModel;
use App\Model\OffreModel;
use App\Model\StatsModel;

/**
 * Account controller.
 *
 * Routes authenticated users to the right panel based on their role.
 * Also handles student-only company rating submission.
 */
class AccountController
{
    /**
     * Display the appropriate panel for the authenticated user.
     */
    public function index()
    {
        Auth::requireAuth();

        $user = Auth::user();
        $id   = $user['Id_user'];       
        $role = $user['Role'];          
 
        switch ($role) {
            case 0: // Student
                $pannelModel = new PannelModel();

                $infos = $pannelModel->getUserInfos($id);
                View::render('pannel_eleve.html.twig', [
                    'infos' => $infos,
                ]);
                return;

            case 1: // Pilot
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

                // Pre-compute dashboard stats for the admin panel.
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

    public function noterEntreprise(): void
    {
                // If not logged in, force authentication before allowing rating.
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        $user = Auth::user();
                // Only students (Role = 0) are allowed to rate.
        if ((int) ($user['Role'] ?? -1) !== 0) {
            header('Location: /forbidden');
            exit;
        }

                // Rating must come from a POST form submission.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $idOffre = InputValidator::getInt($_POST, 'id_offre', 0, 1);
        $idEntreprise = InputValidator::getInt($_POST, 'id_entreprise', 0, 1);
        $note = InputValidator::getInt($_POST, 'note', 0, 1, 5);

        // Always return to the offer page.
        $redirectUrl = '/candidater?id_offre=' . max(0, $idOffre);

        // Validate received parameters.
        if ($idOffre <= 0 || $idEntreprise <= 0 || $note < 1 || $note > 5) {
            header('Location: ' . $redirectUrl . '&rating=invalid');
            exit;
        }

        $entrepriseModel = new EntrepriseModel();

        // Student can only rate a company they have applied to.
        if (!$entrepriseModel->canUserRateEntreprise((int) $user['Id_user'], $idEntreprise)) {
            header('Location: ' . $redirectUrl . '&rating=forbidden');
            exit;
        }

        // Save rating (insert or update depending on whether it already exists).
        $entrepriseModel->noterEntreprise((int) $user['Id_user'], $idEntreprise, $note);

        header('Location: ' . $redirectUrl . '&rating=saved');
        exit;
    }
}
