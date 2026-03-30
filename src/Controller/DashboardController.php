<?php

namespace App\Controller;

use App\Model\UtilisateurModel;
use App\Model\EntrepriseModel;
use App\Model\OffreModel;
use App\Core\Auth;
use App\Core\View;
use App\Model\StatsModel;

class DashboardController
{
    public function __construct()
    {
        Auth::requireAuth();
        
        $user = Auth::user();
        $role = (int)($user['Role'] ?? 0);
        
        if ($role < 1) {
            header('Location: /forbidden');
            exit;
        }
    }

    public function index()
    {
        $model = new UtilisateurModel();
        $user  = Auth::user();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;

            if ($action === 'add') {
                $model->create($_POST);
                header('Location: /student_list');
                exit;
            }
            if ($action === 'edit') {
                $model->update($_POST['id'], $_POST);
                header('Location: /student_list');
                exit;
            }
            if ($action === 'delete') {
                $model->delete($_POST['id']);
                header('Location: /student_list');
                exit;
            }
        }

        if (isset($_GET['id'])) {
            $userId = (int)$_GET['id'];
            $editUser = $model->getById($userId);

            $isPilot = ((int)($user['Role'] ?? 0) === 1);
            $isOwnedStudent = $editUser
                && (int)($editUser['Role'] ?? -1) === 0
                && (int)($editUser['est_gere_par'] ?? 0) === (int)$user['Id_user'];

            if (!$isPilot || !$isOwnedStudent) {
                header('Location: /forbidden');
                exit;
            }

            $stagesEleve = $model->getCandidatures($userId);

            View::render('eleve.html.twig', [
                'editUser'     => $editUser,
                'pilote_id'    => $user['Id_user'],
                'session_role' => $user['Role'],
                'stagesEleve'  => $stagesEleve
            ]);
            return;
        }

        $role = (int)($user['Role'] ?? 0);
        $users = ($role === 2) 
            ? $model->getByRole(0) 
            : $model->getByRoleAndEstGerePar(0, $user['Id_user']);

        View::render('liste_eleves.html.twig', [
            'users' => $users
        ]);
    }

    public function addEleve()
    {
        $user = Auth::user();
        $pilotesModel = new UtilisateurModel();
        $pilotes = $pilotesModel->getByRole(1);

        View::render('eleve.html.twig', [
            'editUser'     => null,
            'pilote_id'    => $user['Id_user'],
            'session_role' => $user['Role'],
            'pilotes'      => $pilotes
        ]);
    }


    public function Entreprise()
    {
        $model = new EntrepriseModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;

            if ($action === 'edit') {
                $model->update((int)$_POST['id'], $_POST);
                header('Location: /enterprise_list');
                exit;
            }

            if ($action === 'add') {
                $model->create($_POST);
                header('Location: /enterprise_list');
                exit;
            }

            if ($action === 'delete') {
                $model->delete((int)$_POST['id']);
                header('Location: /enterprise_list');
                exit;
            }
        }

        $editEntreprise = null;
        if (isset($_GET['id'])) {
            $editEntreprise = $model->getById((int)$_GET['id']);
        }

        View::render('entreprise.html.twig', [
            'editEntreprise' => $editEntreprise,
        ]);
    }

    public function addEntreprise()
    {
        View::render('entreprise.html.twig', [
            'editEntreprise' => null,
        ]);
    }

    public function listEntreprise()
    {
        $model = new EntrepriseModel();

        $entreprise = $model->getAll();

        View::render('liste_entreprises.html.twig', [
            'entreprises' => $entreprise
        ]);
    }

    public function Offre()
    {
        $model = new OffreModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;

            if ($action === 'edit') {
                $model->update((int)$_POST['id'], $_POST);
                header('Location: /offer_list');
                exit;
            }

            if ($action === 'add') {
                $model->create($_POST);
                header('Location: /offer_list');
                exit;
            }

            if ($action === 'delete') {
                $model->delete((int)$_POST['id']);
                header('Location: /offer_list');
                exit;
            }
        }

        $editOffre = null;
        if (isset($_GET['id'])) {
            $editOffre = $model->getById((int)$_GET['id']);
        }

        $model_entreprise = new EntrepriseModel();
        View::render('offre.html.twig', [
            'editOffre'    => $editOffre,
            'entreprises'  => $model_entreprise->getAll(),
        ]);
    }

    public function listOffre()
    {
        $model = new OffreModel();
        $offres = $model->getAll();
        View::render('liste_offres.html.twig', [
            'offres' => $offres
        ]);
    }

    public function listPilotes()
    {
        $model = new UtilisateurModel();
        $pilotes = $model->getByRole(1);
        View::render('liste_pilotes.html.twig', [
            'pilotes' => $pilotes
        ]);
    }

    public function Pilote()
    {
        $model = new UtilisateurModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? null;

            if ($action === 'edit') {
                $model->update((int)$_POST['id'], $_POST);
                header('Location: /pilotes_list');
                exit;
            }

            if ($action === 'add') {
                $model->create($_POST);
                header('Location: /pilotes_list');
                exit;
            }

            if ($action === 'delete') {
                $model->delete((int)$_POST['id']);
                header('Location: /pilotes_list');
                exit;
            }
        }

        $editUser = null;
        if (isset($_GET['id'])) {
            $editUser = $model->getById((int)$_GET['id']);
        }

        $user = Auth::user();
        View::render('pilotes.html.twig', [
            'editUser'     => $editUser,
            'pilote_id'    => $user['Id_user'],
            'session_role' => $user['Role']
        ]);
    }

    public function addPilote()
    {
    $user = Auth::user();
    View::render('pilotes.html.twig', [
        'editUser'     => null,
        'pilote_id'    => $user['Id_user'],
        'session_role' => $user['Role']
    ]);
    }

    public function dashboard(): void
    {
        $statsModel = new StatsModel();

        $offerId = null;
        if (isset($_GET['id-offre'])) {
            $offerId = (int) $_GET['id-offre'];
        } elseif (isset($_GET['id_offre'])) {
            $offerId = (int) $_GET['id_offre'];
        } elseif (isset($_GET['id'])) {
            $offerId = (int) $_GET['id'];
        }

        $offerStats = null;
        if ($offerId && $offerId > 0) {
            $offerStats = $statsModel->getOfferStats($offerId) ?: null;
        }

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

        View::render('dashboard.html.twig', [
            'statsOffres' => $statsOffres,
            'statsEntreprises' => $statsEntreprises,
            'statsEleves' => $statsEleves,
            'offerStats' => $offerStats,
            'offerId'    => $offerId,
        ]);
    }
}
