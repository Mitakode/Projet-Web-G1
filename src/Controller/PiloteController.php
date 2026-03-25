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
            header('Location: /account');
            exit;
        }

        if ($action === 'edit') {
            $model->update($_POST['id'], $_POST);
            header('Location: /account');
            exit;
        }

        if ($action === 'delete') {
            $model->delete($_POST['id']);
            header('Location: /account');
            exit;
        }

        $users = $model->getByRoleandestgerepar(0,$_SESSION['user']['Id_user']);

        $editUser = null;
        if (isset($_GET['edit'])) {
            $editUser = $model->getById($_GET['edit']);
        }

        View::render('liste_eleves.html.twig', [
            'edituser'  => $editUser,
            'users' => $users
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