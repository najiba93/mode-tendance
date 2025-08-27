<?php
 
namespace App\Controller;
 
use App\Entity\Produit;
use App\Entity\Commande;
use App\Entity\CommandeProduit;
use App\Form\CommandeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
 
/**
 * Contrôleur pour gérer le panier d'achat
 * Ce fichier gère tout ce qui concerne le panier : ajouter, supprimer, modifier, commander
 */
class PanierController extends AbstractController
{
    /**
     * AJOUTER UN PRODUIT AU PANIER
     * Route : /panier/ajouter/{id}
     * Méthode : POST (formulaire)
     */
    #[Route('/panier/ajouter/{id}', name: 'ajouter_au_panier', methods: ['POST'])]
    public function ajouter(Produit $produit, Request $request, SessionInterface $session): Response
    {
        // 1. Récupérer la quantité depuis le formulaire (par défaut = 1)
        $quantite = (int) $request->request->get('quantite', 1);
 
        // 2. Récupérer le panier actuel depuis la session
        $panier = $session->get('panier', []);
 
        // 3. Ajouter ou augmenter la quantité du produit
        $produitId = $produit->getId();
        if (isset($panier[$produitId])) {
            // Si le produit existe déjà, on ajoute la nouvelle quantité
            $panier[$produitId] += $quantite;
        } else {
            // Si c'est un nouveau produit, on l'ajoute
            $panier[$produitId] = $quantite;
        }
 
        // 4. Sauvegarder le panier mis à jour dans la session
        $session->set('panier', $panier);
 
        // 5. Afficher un message de succès
        $this->addFlash('success', 'Produit ajouté au panier !');
 
        // 6. Rediriger vers la page du produit
        return $this->redirectToRoute('produits_carte', ['id' => $produit->getId()]);
    }
 
    /**
     * AFFICHER LE PANIER
     * Route : /Panier
     * Méthode : GET
     */
    #[Route('/Panier', name: 'Panier')]
    public function index(SessionInterface $session, EntityManagerInterface $em): Response
    {
        // 1. Récupérer le panier depuis la session
        $panier = $session->get('panier', []);
 
        // 2. Préparer les données pour l'affichage
        $produitsDuPanier = [];
        $totalGeneral = 0;
 
        // 3. Pour chaque produit dans le panier
        foreach ($panier as $produitId => $quantite) {
            // Récupérer les détails du produit depuis la base de données
            $produit = $em->getRepository(Produit::class)->find($produitId);
 
            if ($produit) {
                // Calculer le sous-total pour ce produit
                $sousTotal = $produit->getPrix() * $quantite;
 
                // Ajouter les informations du produit
                $produitsDuPanier[] = [
                    'produit' => $produit,
                    'quantite' => $quantite,
                    'sousTotal' => $sousTotal,
                ];
 
                // Ajouter au total général
                $totalGeneral += $sousTotal;
            }
        }
 
        // 4. Afficher la page du panier
        return $this->render('panier/index.html.twig', [
            'panier' => $produitsDuPanier,
            'total' => $totalGeneral
        ]);
    }
 
    /**
     * SUPPRIMER UN PRODUIT DU PANIER
     * Route : /panier/supprimer/{id}
     * Méthode : GET
     */
    #[Route('/panier/supprimer/{id}', name: 'supprimer_du_panier')]
    public function supprimer(int $id, SessionInterface $session): Response
    {
        // 1. Récupérer le panier actuel
        $panier = $session->get('panier', []);
 
        // 2. Supprimer le produit du panier
        unset($panier[$id]);
 
        // 3. Sauvegarder le panier mis à jour
        $session->set('panier', $panier);
 
        // 4. Afficher un message de confirmation
        $this->addFlash('success', 'Produit supprimé du panier !');
 
        // 5. Rediriger vers le panier
        return $this->redirectToRoute('Panier');
    }
 
    /**
     * MODIFIER LA QUANTITÉ D'UN PRODUIT
     * Route : /panier/modifier-quantite/{id}
     * Méthode : POST
     */
    #[Route('/panier/modifier-quantite/{id}', name: 'modifier_quantite_panier', methods: ['POST'])]
    public function modifierQuantite(Request $request, int $id, SessionInterface $session): Response
    {
        // 1. Récupérer l'action (plus ou moins)
        $action = $request->request->get('action');
 
        // 2. Récupérer le panier actuel
        $panier = $session->get('panier', []);
 
        // 3. Modifier la quantité selon l'action
        if ($action === 'plus') {
            // Augmenter la quantité
            $panier[$id] = ($panier[$id] ?? 0) + 1;
        } elseif ($action === 'moins') {
            // Diminuer la quantité (minimum 1)
            if (isset($panier[$id]) && $panier[$id] > 1) {
                $panier[$id]--;
            }
        }
 
        // 4. Sauvegarder le panier mis à jour
        $session->set('panier', $panier);
 
        // 5. Afficher un message de confirmation
        $this->addFlash('success', 'Quantité mise à jour !');
 
        // 6. Rediriger vers le panier
        return $this->redirectToRoute('Panier');
    }
 
    /**
     * FINALISER LA COMMANDE
     * Route : /panier/commander
     * Méthode : GET et POST
     */
    #[Route('/panier/commander', name: 'finaliser_commande', methods: ['GET', 'POST'])]
    public function finaliserCommande(Request $request, SessionInterface $session, EntityManagerInterface $em): Response
    {
        // 1. Vérifier que le panier n'est pas vide
        $panier = $session->get('panier', []);
        if (empty($panier)) {
            $this->addFlash('warning', 'Votre panier est vide !');
            return $this->redirectToRoute('Panier');
        }
 
        // 2. Calculer le total et préparer les détails
        $totalGeneral = 0;
        $produitsDuPanier = [];
 
        foreach ($panier as $produitId => $quantite) {
            $produit = $em->getRepository(Produit::class)->find($produitId);
            if ($produit) {
                $sousTotal = $produit->getPrix() * $quantite;
                $produitsDuPanier[] = [
                    'produit' => $produit,
                    'quantite' => $quantite,
                    'sousTotal' => $sousTotal,
                ];
                $totalGeneral += $sousTotal;
            }
        }
 
        // 3. Créer une nouvelle commande
        $commande = new Commande();
        $commande->setDate(new \DateTime());
        $commande->setTotal($totalGeneral);
        $commande->setCommande('CMD-' . uniqid());
 
        // 4. Si l'utilisateur est connecté, l'associer à la commande
        $user = $this->getUser();
        if ($user) {
            $commande->setUser($user);
 
            // Pré-remplir les informations si disponibles
            try {
                // Nom complet
                $nomComplet = $user->getNom();
                if (!$nomComplet && method_exists($user, 'getFirstName') && method_exists($user, 'getLastName')) {
                    $nomComplet = trim($user->getFirstName() . ' ' . $user->getLastName());
                }
                if ($nomComplet) {
                    $commande->setNom($nomComplet);
                }
 
                // Autres informations
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
                // En cas d'erreur, continuer sans pré-remplir
            }
        }
 
        // 5. Créer le formulaire de commande
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);
 
        // 6. Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Si l'adresse de livraison est vide, utiliser l'adresse de facturation
            if (empty($commande->getAdresseLivraison())) {
                $commande->setAdresseLivraison($commande->getAdressePostale());
            }
 
            // 7. Créer les lignes de commande pour chaque produit
            foreach ($panier as $produitId => $quantite) {
                $produit = $em->getRepository(Produit::class)->find($produitId);
                if (!$produit) {
                    continue;
                }
 
                // Créer une nouvelle ligne de commande
                $ligneCommande = new CommandeProduit();
                $ligneCommande->setCommande($commande);
                $ligneCommande->setProduit($produit);
                $ligneCommande->setQuantite($quantite);
                $ligneCommande->setSousTotal($produit->getPrix() * $quantite);
 
                // Ajouter la ligne à la commande
                $commande->addCommandeProduit($ligneCommande);
                $em->persist($ligneCommande);
            }
 
            // 8. Sauvegarder la commande dans la base de données
            $em->persist($commande);
            $em->flush();
 
            // 9. Vider le panier
            $session->remove('panier');
 
            // 10. Rediriger vers la page de confirmation
            return $this->redirectToRoute('commande_confirmation', [
                'id' => $commande->getId()
            ]);
        }
 
        // 11. Afficher le formulaire de commande
        return $this->render('panier/commande.html.twig', [
            'form' => $form->createView(),
            'panier' => $produitsDuPanier,
            'total' => $totalGeneral
        ]);
    }
 
    /**
     * PAGE DE CONFIRMATION DE COMMANDE
     * Route : /panier/confirmation/{id}
     * Méthode : GET
     */
    #[Route('/panier/confirmation/{id}', name: 'confirmation_commande')]
    public function confirmationCommande(Commande $commande, EntityManagerInterface $em): Response
    {
        // 1. Préparer les détails de la commande pour l'affichage
        $produitsCommande = [];
 
        // 2. Pour chaque produit de la commande
        foreach ($commande->getCommandeProduits() as $ligneCommande) {
            $produit = $ligneCommande->getProduit();
            $quantite = $ligneCommande->getQuantite();
            $sousTotal = $produit->getPrix() * $quantite;
 
            $produitsCommande[] = [
                'produit' => $produit,
                'quantite' => $quantite,
                'sousTotal' => $sousTotal,
            ];
        }
 
        // 3. Afficher la page de confirmation
        return $this->render('panier/confirmation.html.twig', [
            'commande' => $commande,
            'panier' => $produitsCommande
        ]);
    }
}