<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    #[Route('/Panier', name: 'Panier')]
    public function index(): Response
    {
        return $this->render('Panier/index.html.twig');
    }
}
