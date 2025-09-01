<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ========================================
 * CONTRÔLEUR DE LA PAGE D'ACCUEIL
 * ========================================
 * 
 * Ce contrôleur gère l'affichage de la page principale de l'application.
 * Il récupère les catégories de produits pour les afficher dans le header.
 * 
 * @final Cette classe ne peut pas être étendue
 */
final class HomeController extends AbstractController
{
    /**
     * ========================================
     * PAGE D'ACCUEIL PRINCIPALE
     * ========================================
     * 
     * Route : / (racine du site)
     * Nom : Accueil
     * 
     * Cette méthode affiche la page d'accueil avec :
     * - Le carrousel d'images
     * - Les catégories de produits dans le header
     * - Le contenu principal de présentation
     * 
     * @param CategorieRepository $categorieRepository Repository pour récupérer les catégories
     * @return Response Page d'accueil rendue
     */
    #[Route('/', name: 'Accueil')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        // ========================================
        // RÉCUPÉRATION DES DONNÉES
        // ========================================
        
        // Récupère toutes les catégories de produits
        // Utilisées pour afficher le menu de navigation des catégories
        $categories = $categorieRepository->findAll();

        // ========================================
        // RENDU DE LA PAGE
        // ========================================
        
        // Retourne la page d'accueil avec les catégories
        // Le template 'Accueil/index.html.twig' sera utilisé
        return $this->render('Accueil/index.html.twig', [
            'categories' => $categories, // Passé au template pour affichage
        ]);
    }
}
