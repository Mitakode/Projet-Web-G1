<?php

namespace App\Controller;

use App\Model\UtilisateurModel;
use App\Model\EntrepriseModel;
use App\Model\OffreModel;
use App\Core\View;

class PiloteController
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
                'editUser' => $editUser,
                'pilote_id' => $_SESSION['user']['Id_user'] ?? null
            ]);
            return;
        }

        $users = $model->getByRoleandestgerepar(0, $_SESSION['user']['Id_user']);
        View::render('liste_eleves.html.twig', [
            'users' => $users
        ]);
    }

    public function addEleve()
    {
        View::render('eleve.html.twig', [
            'editUser' => null,
            'pilote_id' => $_SESSION['user']['Id_user'] ?? null
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

    public function listOffre()
    {
        $model = new OffreModel();

        $offres = $model->getAll();

        View::render('liste_offres.html.twig', [
            'offres' => $offres
        ]);
    }
}