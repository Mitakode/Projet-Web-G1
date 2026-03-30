<?php

namespace App\Controller;

use App\Core\InputValidator;
use App\Core\View;
use App\Model\OffreModel;

class HomeController
{
    public function index()
    {
        $offreModel = new OffreModel();
        $searchQuery = trim((string) ($_GET['query'] ?? ''));
        $searchInputInvalid = false;
        // Recherche: limite les caractères pour éviter les entrées parasites.
        if (!InputValidator::regex($searchQuery, '/^[\p{L}\p{N}\s\-\'".,()@]{0,100}$/u')) {
            $searchQuery = '';
            $searchInputInvalid = true;
        }

        $elementsParPage = 10;

        $pageActuelle = InputValidator::getInt($_GET, 'page', 1, 1);
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

        $userId = $_SESSION['user']['Id_user'] ?? null; 
        
        $userRole = isset($_SESSION['user']['Role']) ? (int)$_SESSION['user']['Role'] : null; 

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
            'searchInputInvalid' => $searchInputInvalid,
            'isStudent'    => $isStudent,
            'session_role' => $_SESSION['user']['Role'] ?? null,
            'isAuth'       => isset($_SESSION['user']),
        ]);
    }

public function addWishlist()
    {
        header('Content-Type: application/json');
        
        try {
            $offerId = InputValidator::getInt($_GET, 'Id_offre', 0, 1);
            $userId = isset($_SESSION['user']['Id_user']) ? (int)$_SESSION['user']['Id_user'] : null;
            $userRole = isset($_SESSION['user']['Role']) ? (int)$_SESSION['user']['Role'] : null;
            
            if ($offerId > 0 && $userId && $userRole === 0) {
                $offreModel = new \App\Model\OffreModel();
                
                if (!$offreModel->isInWishlist($offerId, $userId)) {
                    $offreModel->addWishlist($offerId, $userId);
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => true]);
                }
                exit;
            }
            
            echo json_encode(['success' => false]);
            exit;
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false]);
            exit;
        }
    }

    public function deleteWishlist()
    {
        header('Content-Type: application/json');
        
        try {
            $offerId = InputValidator::getInt($_GET, 'Id_offre', 0, 1);
            $userId = isset($_SESSION['user']['Id_user']) ? (int)$_SESSION['user']['Id_user'] : null;
            $userRole = isset($_SESSION['user']['Role']) ? (int)$_SESSION['user']['Role'] : null;

            if ($offerId > 0 && $userId && $userRole === 0) {
                $offreModel = new \App\Model\OffreModel();
                $offreModel->removeFromWishlist($userId, $offerId);
                echo json_encode(['success' => true]);
                exit;
            }
            
            echo json_encode(['success' => false]);
            exit;

        } catch (\Exception $e) {
            echo json_encode(['success' => false]);
            exit;
        }
    }
}