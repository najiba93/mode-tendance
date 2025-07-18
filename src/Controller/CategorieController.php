<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

}
