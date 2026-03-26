<?php

namespace App\Controller;

use App\Core\View;
use App\Model\OffreModel;

class HomeController
{
    public function index()
    {
        $offreModel = new OffreModel();
        $searchQuery = isset($_GET['query']) ? trim((string) $_GET['query']) : '';

        $elementsParPage = 10;

        $pageActuelle = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($pageActuelle < 1) {
            $pageActuelle = 1;
        }

        $totalElements = $offreModel->getTotalOffres($searchQuery);
        $totalPages = ceil($totalElements / $elementsParPage);
        if ($totalPages < 1) {
            $totalPages = 1;
        }

        if ($pageActuelle > $totalPages) {
            $pageActuelle = $totalPages;
        }

        $offset = ($pageActuelle - 1) * $elementsParPage;

        $offres = $offreModel->getOffresPaginated($elementsParPage, $offset, $searchQuery);

        $userId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['role'] ?? null; 

        $isStudent = ($userId && $userRole === 0);

        if ($isStudent) {
            foreach ($offres as &$offre) {
                $offre['is_in_wishlist'] = false; 
                $wishlistEntry = $offreModel->isInWishlist($offre['Id_offre'], $userId);
                
                if ($wishlistEntry) {
                    $offre['is_in_wishlist'] = true;
                }
            }
            unset($offre);
        }

        View::render('home.html.twig', [
            'offres'       => $offres,
            'totalPages'   => $totalPages,
            'pageActuelle' => $pageActuelle,
            'searchQuery'  => $searchQuery,
            'isStudent'    => $isStudent,
        ]);
    }

    public function addWishlist()
    {
        $offerId = $_GET['Id_offre'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['role'] ?? null;
        
        if ($offerId && $userId && $userRole === 0) {
            $offreModel = new OffreModel();
            if (!$offreModel->isInWishlist($offerId, $userId)) {
                $offreModel->addWishlist($offerId, $userId);
            }
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['success' => false]);
        exit;
    }

    public function deleteWishlist()
    {
        $offerId = $_GET['Id_offre'] ?? null;
        $userId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['role'] ?? null;

        if ($offerId && $userId && $userRole === 0) {
            $offreModel = new OffreModel();
            $offreModel->removeFromWishlist($userId, $offerId);
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['success' => false]);
        exit;
    }
}