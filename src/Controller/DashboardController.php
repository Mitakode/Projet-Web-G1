<?php

namespace App\Controller;

use App\Model\UtilisateurModel;
use App\Model\EntrepriseModel;
use App\Model\OffreModel;
use App\Core\View;

class DashboardController
{
    public function index()
    {
        $model = new UtilisateurModel();

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

         $editUser = null;
        if (isset($_GET['id'])) {
            $editUser = $model->getById((int)$_GET['id']);
            View::render('eleve.html.twig', [
                'editUser'     => $editUser,
                'pilote_id'    => $_SESSION['user']['Id_user'] ?? null,
                'session_role' => $_SESSION['user']['Role'] ?? null
            ]);
            return; 
        }

        $role = $_SESSION['user']['Role'] ?? null;

        if ($role === 2) {
            $users = $model->getByRole(0);
        } else {
            $users = $model->getByRoleandestgerepar(0, $_SESSION['user']['Id_user']);
        }
        View::render('liste_eleves.html.twig', [
            'users' => $users
        ]);
}

    public function addEleve()
    {
        View::render('eleve.html.twig', [
            'editUser'     => null,
            'pilote_id'    => $_SESSION['user']['Id_user'] ?? null,
            'session_role' => $_SESSION['user']['Role'] ?? null
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

        View::render('offre.html.twig', [
            'editOffre' => $editOffre,
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

        View::render('pilotes.html.twig', [
            'editUser'     => $editUser,
            'pilote_id'    => $_SESSION['user']['Id_user'] ?? null,
            'session_role' => $_SESSION['user']['Role'] ?? null
        ]);
    }

    public function addPilote()
    {
        View::render('pilotes.html.twig', [
        'editUser'     => null,
        'pilote_id'    => $_SESSION['user']['Id_user'] ?? null,
        'session_role' => $_SESSION['user']['Role'] ?? null
        ]);
    }
}
