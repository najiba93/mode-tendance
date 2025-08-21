<?php

namespace App\Controller;

use App\Entity\Commande;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/panier/confirmation/{id}', name: 'commande_confirmation')]
    public function confirmation(Commande $commande): Response
    {
        $panierAvecDetails = [];

        foreach ($commande->getCommandeProduits() as $commandeProduit) {
            $panierAvecDetails[] = [
                'produit' => $commandeProduit->getProduit(),
                'quantite' => $commandeProduit->getQuantite(),
                'sousTotal' => $commandeProduit->getSousTotal(),
            ];
        }

        return $this->render('panier/confirmation.html.twig', [
            'commande' => $commande,
            'panierAvecDetails' => $panierAvecDetails,
        ]);
    }
}
