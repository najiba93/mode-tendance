<?php

namespace App\Controller;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    // ✅ Ajouter au panier
    #[Route('/panier/ajouter/{id}', name: 'ajouter_au_panier', methods: ['POST'])]
    public function ajouter(Produit $produit, Request $request, SessionInterface $session): Response
    {
        $quantite = (int) $request->request->get('quantite', 1);
        $panier = $session->get('panier', []);
        $panier[$produit->getId()] = ($panier[$produit->getId()] ?? 0) + $quantite;
        $session->set('panier', $panier);

        $this->addFlash('success', 'Produit ajouté au panier');
        return $this->redirectToRoute('produits_carte', ['id' => $produit->getId()]);
    }

    // ✅ Afficher le panier
    #[Route('/Panier', name: 'Panier')]
    public function index(SessionInterface $session, EntityManagerInterface $em): Response
    {
        $panier = $session->get('panier', []);
        $panierAvecDetails = [];
        $total = 0;

        foreach ($panier as $id => $quantite) {
            $produit = $em->getRepository(Produit::class)->find($id);
            if ($produit) {
                $panierAvecDetails[] = [
                    'produit' => $produit,
                    'quantite' => $quantite,
                    'sousTotal' => $produit->getPrix() * $quantite,
                ];
                $total += $produit->getPrix() * $quantite;
            }
        }

        return $this->render('panier/index.html.twig', [
            'panier' => $panierAvecDetails,
            'total' => $total
        ]);
    }

    // ✅ Supprimer du panier
    #[Route('/panier/supprimer/{id}', name: 'supprimer_du_panier')]
    public function supprimer(int $id, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);
        unset($panier[$id]);
        $session->set('panier', $panier);

        $this->addFlash('success', 'Produit supprimé du panier');
        return $this->redirectToRoute('Panier');
    }
}
