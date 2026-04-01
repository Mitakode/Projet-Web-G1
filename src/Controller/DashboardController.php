<?php

namespace App\Controller;

use App\Model\UtilisateurModel;
use App\Model\EntrepriseModel;
use App\Model\OffreModel;
use App\Core\Auth;
use App\Core\InputValidator;
use App\Core\View;
use InvalidArgumentException;
use App\Model\StatsModel;

/**
 * Back-office controller.
 *
 * Provides CRUD screens for students/companies/offers/pilots and an admin stats
 * dashboard. Access is restricted to Pilot (Role=1) and Admin (Role=2).
 */
class DashboardController
{
    private const ITEMS_PER_PAGE = 20;

    /**
     * In-memory pagination helper.
     *
     * This controller often loads items from the DB then paginates the array.
     * (For very large datasets, prefer SQL LIMIT/OFFSET.)
     */
    private function paginateArray(array $items, int $itemsPerPage = self::ITEMS_PER_PAGE): array
    {
        $pageActuelle = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        if ($pageActuelle < 1) {
            $pageActuelle = 1;
        }

        $totalElements = count($items);
        $totalPages = (int) ceil($totalElements / $itemsPerPage);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        if ($pageActuelle > $totalPages) {
            $pageActuelle = $totalPages;
        }

        $offset = ($pageActuelle - 1) * $itemsPerPage;

        return [
            'items' => array_slice($items, $offset, $itemsPerPage),
            'pageActuelle' => $pageActuelle,
            'totalPages' => $totalPages,
            'totalElements' => $totalElements,
        ];
    }

    public function __construct()
    {
        // Back-office requires authentication.
        Auth::requireAuth();
        
        $user = Auth::user();
        $role = (int)($user['Role'] ?? 0);
        
        // Only Pilot/Admin can access this controller.
        if ($role < 1) {
            header('Location: /forbidden');
            exit;
        }
    }

    /**
     * Student list + CRUD actions.
     *
     * - Admin sees all students
     * - Pilot sees only their managed students
     */
    public function index()
    {
        $model = new UtilisateurModel();
        $user  = Auth::user();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // CRUD action restricted to add/edit/delete.
                $action = InputValidator::requireEnum($_POST, 'action', ['add', 'edit', 'delete']);

                if ($action === 'add') {
                    $model->create($_POST);
                    header('Location: /student_list');
                    exit;
                }
                if ($action === 'edit') {
                    // Sanitize ID: positive integer only.
                    $model->update(InputValidator::getInt($_POST, 'id', 0, 1), $_POST);
                    header('Location: /student_list');
                    exit;
                }
                if ($action === 'delete') {
                    // Sanitize ID: positive integer only.
                    $model->delete(InputValidator::getInt($_POST, 'id', 0, 1));
                    header('Location: /student_list');
                    exit;
                }
            } catch (InvalidArgumentException $e) {
                // On validation error, just return to the list.
                header('Location: /student_list');
                exit;
            }
        }

        if (isset($_GET['id'])) {
            // Validate edit ID before DB access.
            $userId = InputValidator::getInt($_GET, 'id', 0, 1);
            $editUser = $model->getById($userId);
            $pilotes = $model->getByRole(1);

            $currentRole = (int)($user['Role'] ?? 0);
            $isAdmin = ($currentRole === 2);
            $isPilot = ($currentRole === 1);
            $isOwnedStudent = $editUser
                && (int)($editUser['Role'] ?? -1) === 0
                && (int)($editUser['est_gere_par'] ?? 0) === (int)$user['Id_user'];

            // Admin: can edit all students. Pilot: only their own students.
            $canEditStudent = $isAdmin || ($isPilot && $isOwnedStudent);

            if (!$canEditStudent) {
                header('Location: /forbidden');
                exit;
            }

            $stagesEleve = $model->getCandidatures($userId);

            View::render('eleve.html.twig', [
                'editUser'     => $editUser,
                'pilote_id'    => $user['Id_user'],
                'session_role' => $user['Role'],
                'stagesEleve'  => $stagesEleve,
                'pilotes'      => $pilotes
            ]);
            return;
        }

        $role = (int)($user['Role'] ?? 0);
        $users = ($role === 2)
            ? $model->getByRole(0)
            : $model->getByRoleAndEstGerePar(0, $user['Id_user']);

        // Filtre recherche
        $search = trim((string)($_GET['search'] ?? ''));
        if ($search !== '') {
            $users = array_filter($users, function($u) use ($search) {
                return stripos($u['Nom'], $search) !== false
                    || stripos($u['Prenom'], $search) !== false
                    || stripos($u['Email'], $search) !== false;
            });
            $users = array_values($users);
        }

        $pagination = $this->paginateArray($users);

        View::render('liste_eleves.html.twig', [
            'users'         => $pagination['items'],
            'pageActuelle'  => $pagination['pageActuelle'],
            'totalPages'    => $pagination['totalPages'],
            'totalElements' => $pagination['totalElements'],
            'search'        => $search,
        ]);
    }

    /**
     * Show the student creation form.
     */
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
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // CRUD action restricted to add/edit/delete.
                $action = InputValidator::requireEnum($_POST, 'action', ['add', 'edit', 'delete']);

                if ($action === 'edit') {
                    // Sanitize ID: positive integer only.
                    $model->update(InputValidator::getInt($_POST, 'id', 0, 1), $_POST);
                    header('Location: /enterprise_list');
                    exit;
                }

                if ($action === 'add') {
                    $model->create($_POST);
                    header('Location: /enterprise_list');
                    exit;
                }

                if ($action === 'delete') {
                    // Sanitize ID: positive integer only.
                    $model->delete(InputValidator::getInt($_POST, 'id', 0, 1));
                    header('Location: /enterprise_list');
                    exit;
                }
            } catch (InvalidArgumentException $e) {
                // Show validation errors on the same page.
                $error = $e->getMessage();
            }
        }

        $editEntreprise = null;
        if (isset($_GET['id'])) {
            // Validate edit ID before DB access.
            $editEntreprise = $model->getById(InputValidator::getInt($_GET, 'id', 0, 1));
        }

        View::render('entreprise.html.twig', [
            'editEntreprise' => $editEntreprise,
            'error' => $error,
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

        $pagination = $this->paginateArray($entreprise);

        View::render('liste_entreprises.html.twig', [
            'entreprises' => $pagination['items'],
            'pageActuelle' => $pagination['pageActuelle'],
            'totalPages' => $pagination['totalPages'],
            'totalElements' => $pagination['totalElements'],
        ]);
    }

    public function Offre()
    {
        $model = new OffreModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // CRUD action restricted to add/edit/delete.
                $action = InputValidator::requireEnum($_POST, 'action', ['add', 'edit', 'delete']);

                if ($action === 'edit') {
                    // Sanitize ID: positive integer only.
                    $model->update(InputValidator::getInt($_POST, 'id', 0, 1), $_POST);
                    header('Location: /offer_list');
                    exit;
                }

                if ($action === 'add') {
                    $model->create($_POST);
                    header('Location: /offer_list');
                    exit;
                }

                if ($action === 'delete') {
                    // Sanitize ID: positive integer only.
                    $model->delete(InputValidator::getInt($_POST, 'id', 0, 1));
                    header('Location: /offer_list');
                    exit;
                }
            } catch (InvalidArgumentException $e) {
                // On validation error, return to list.
                header('Location: /offer_list');
                exit;
            }
        }

        $editOffre = null;
        if (isset($_GET['id'])) {
            // Validate edit ID before DB access.
            $editOffre = $model->getById(InputValidator::getInt($_GET, 'id', 0, 1));
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

        $pagination = $this->paginateArray($offres);

        View::render('liste_offres.html.twig', [
            'offres' => $pagination['items'],
            'pageActuelle' => $pagination['pageActuelle'],
            'totalPages' => $pagination['totalPages'],
            'totalElements' => $pagination['totalElements'],
        ]);
    }

    public function listPilotes()
    {
        $model = new UtilisateurModel();
        $search = trim((string)($_GET['search'] ?? ''));
        
        $pilotes = $model->getByRole(1);
        
        // Filtre en PHP si recherche
        if ($search !== '') {
            $pilotes = array_filter($pilotes, function($p) use ($search) {
                return stripos($p['Nom'], $search) !== false
                    || stripos($p['Prenom'], $search) !== false
                    || stripos($p['Email'], $search) !== false;
            });
            $pilotes = array_values($pilotes);
        }

        $pagination = $this->paginateArray($pilotes);

        View::render('liste_pilotes.html.twig', [
            'pilotes'       => $pagination['items'],
            'pageActuelle'  => $pagination['pageActuelle'],
            'totalPages'    => $pagination['totalPages'],
            'totalElements' => $pagination['totalElements'],
            'search'        => $search,
        ]);
    }

    public function Pilote()
    {
        $model = new UtilisateurModel();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // CRUD action restricted to add/edit/delete.
                $action = InputValidator::requireEnum($_POST, 'action', ['add', 'edit', 'delete']);

                if ($action === 'edit') {
                    // Sanitize ID: positive integer only.
                    $model->update(InputValidator::getInt($_POST, 'id', 0, 1), $_POST);
                    header('Location: /pilotes_list');
                    exit;
                }

                if ($action === 'add') {
                    $model->create($_POST);
                    header('Location: /pilotes_list');
                    exit;
                }

                if ($action === 'delete') {
                    // Sanitize ID: positive integer only.
                    $model->delete(InputValidator::getInt($_POST, 'id', 0, 1));
                    header('Location: /pilotes_list');
                    exit;
                }
            } catch (InvalidArgumentException $e) {
                header('Location: /pilotes_list');
                exit;
            }
        }

        $editUser = null;
        if (isset($_GET['id'])) {
            // Validate edit ID before DB access.
            $editUser = $model->getById(InputValidator::getInt($_GET, 'id', 0, 1));
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

        // Accept multiple query parameter names for the same "offer id" concept.
        // This improves compatibility with links/forms using different conventions.
        $offerId = InputValidator::getInt($_GET, 'id-offre', 0, 1);
        if ($offerId <= 0) {
            $offerId = InputValidator::getInt($_GET, 'id_offre', 0, 1);
        }
        if ($offerId <= 0) {
            $offerId = InputValidator::getInt($_GET, 'id', 0, 1);
        }
        if ($offerId <= 0) {
            $offerId = null;
        }

        $offerStats = null;
        if ($offerId && $offerId > 0) {
            // Fetch per-offer metrics when an offer id is selected.
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
