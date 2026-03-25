<?php

namespace App\Controller;

use App\Core\View;
use App\Model\OffreModel;

class OffreController
{
    public function index()
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
}