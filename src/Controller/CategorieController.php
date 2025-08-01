<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Categorie;
use App\Repository\ProduitRepository;

class CategorieController extends AbstractController
{
    #[Route('/categories', name: 'categorie')]
    public function index(CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findAll();

        return $this->render('categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/categorie/{id}', name: 'categorie_produits')]
    public function produits(Categorie $categorie, ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findBy(['categorie' => $categorie]);

        return $this->render('categories/produits.html.twig', [
            'categorie' => $categorie,
            'produits' => $produits,
        ]);
    }
}
