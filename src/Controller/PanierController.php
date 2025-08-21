<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Commande;
use App\Form\CommandeType;
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

    // ✅ Modifier la quantité dans le panier
    #[Route('/panier/modifier-quantite/{id}', name: 'modifier_quantite_panier', methods: ['POST'])]
    public function modifierQuantite(Request $request, int $id, SessionInterface $session): Response
    {
        $action = $request->request->get('action'); // "plus" ou "moins"
        $panier = $session->get('panier', []);

        if ($action === 'plus') {
            $panier[$id] = ($panier[$id] ?? 0) + 1;
        } elseif ($action === 'moins' && isset($panier[$id]) && $panier[$id] > 1) {
            $panier[$id]--;
        }

        $session->set('panier', $panier);

        $this->addFlash('success', 'Quantité mise à jour');
        return $this->redirectToRoute('Panier');
    }

    // ✅ Finaliser la commande
    #[Route('/panier/commander', name: 'finaliser_commande', methods: ['GET', 'POST'])]
    public function finaliserCommande(Request $request, SessionInterface $session, EntityManagerInterface $em): Response
    {
        $panier = $session->get('panier', []);
        
        if (empty($panier)) {
            $this->addFlash('warning', 'Votre panier est vide');
            return $this->redirectToRoute('Panier');
        }

        // Calculer le total
        $total = 0;
        $panierAvecDetails = [];
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

        // Créer la commande
        $commande = new Commande();
        $commande->setDate(new \DateTime());
        $commande->setTotal($total);
        $commande->setCommande('CMD-' . uniqid());
        
        // Si l'utilisateur est connecté, l'associer
        $user = $this->getUser();
        if ($user) {
            $commande->setUser($user);
            // Pré-remplir avec les données de l'utilisateur si disponibles
            try {
                $nomComplet = $user->getNom();
                if (!$nomComplet && method_exists($user, 'getFirstName') && method_exists($user, 'getLastName')) {
                    $nomComplet = trim($user->getFirstName() . ' ' . $user->getLastName());
                }
                if ($nomComplet) {
                    $commande->setNom($nomComplet);
                }
                
                if (method_exists($user, 'getAdressePostale')) {
                    $commande->setAdressePostale($user->getAdressePostale());
                }
                
                if (method_exists($user, 'getTelephone')) {
                    $commande->setTelephone($user->getTelephone());
                }
                
                if (method_exists($user, 'getAdresseLivraison')) {
                    $commande->setAdresseLivraison($user->getAdresseLivraison());
                }
            } catch (\Exception $e) {
                // En cas d'erreur, on continue sans pré-remplir
            }
        }

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si l'adresse de livraison est vide, utiliser l'adresse de facturation
            if (empty($commande->getAdresseLivraison())) {
                $commande->setAdresseLivraison($commande->getAdressePostale());
            }

            // Sauvegarder la commande
            $em->persist($commande);
            $em->flush();

            // Vider le panier
            $session->remove('panier');

            // Rediriger vers la page de confirmation
            return $this->redirectToRoute('confirmation_commande', [
                'id' => $commande->getId()
            ]);
        }

        return $this->render('panier/commande.html.twig', [
            'form' => $form->createView(),
            'panier' => $panierAvecDetails,
            'total' => $total
        ]);
    }

    // ✅ Page de confirmation de commande
    #[Route('/panier/confirmation/{id}', name: 'confirmation_commande')]
    public function confirmationCommande(Commande $commande, EntityManagerInterface $em): Response
    {
        // Récupérer les détails de la commande depuis la session ou la base de données
        // Pour cet exemple, on va simuler les données du panier
        $panierAvecDetails = [];
        
        // Ici vous pourriez récupérer les détails de la commande depuis une table de liaison
        // Pour l'instant, on affiche juste les informations de base
        
        return $this->render('panier/confirmation.html.twig', [
            'commande' => $commande,
            'panier' => $panierAvecDetails
        ]);
    }
}
