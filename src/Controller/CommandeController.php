<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeProduit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/panier/confirmation/{id}', name: 'commande_confirmation')]
    public function confirmation(Commande $commande, EntityManagerInterface $em): Response
    {
        // Tenter de charger explicitement les lignes de commande (évite problèmes de lazy-loading)
        $lignes = $em->getRepository(CommandeProduit::class)->findBy(['commande' => $commande]);

        $items = [];
        if (!empty($lignes)) {
            foreach ($lignes as $commandeProduit) {
                $items[] = [
                    'produit' => $commandeProduit->getProduit(),
                    'quantite' => $commandeProduit->getQuantite(),
                    'sousTotal' => $commandeProduit->getSousTotal(),
                ];
            }
        } else {
            // Repli: utiliser la relation si disponible
            foreach ($commande->getCommandeProduits() as $commandeProduit) {
                $items[] = [
                    'produit' => $commandeProduit->getProduit(),
                    'quantite' => $commandeProduit->getQuantite(),
                    'sousTotal' => $commandeProduit->getSousTotal(),
                ];
            }
        }

        return $this->render('panier/confirmation.html.twig', [
            'commande' => $commande,
            'panier' => $items,
            'panierAvecDetails' => $items,
        ]);
    }
}
