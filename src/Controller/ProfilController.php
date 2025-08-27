<?php
 
 
namespace App\Controller;
 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Commande;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
 
/**
 * Contrôleur pour gérer le profil utilisateur
 * Ce fichier gère tout ce qui concerne le profil : affichage, modification, commandes
 */
final class ProfilController extends AbstractController
{
    /**
     * PAGE PROFIL UTILISATEUR
     * Route : /profil
     * Méthode : GET et POST
     * Accès : Utilisateur connecté
     */
    #[Route('/profil', name: 'profil')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // 1. Récupérer l'utilisateur connecté
        $utilisateur = $this->getUser();
 
        // 2. Créer le formulaire de modification du profil
        $formulaire = $this->createForm(UserType::class, $utilisateur);
        $formulaire->handleRequest($request);
 
        // 3. Si le formulaire est soumis et valide
        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            // 4. Sauvegarder les modifications dans la base de données
            $em->flush();
 
            // 5. Afficher un message de succès
            $this->addFlash('success', 'Informations modifiées avec succès !');
 
            // 6. Rediriger vers la page de profil
            return $this->redirectToRoute('profil');
        }
 
        // 7. Récupérer les commandes de l'utilisateur
        $commandesUtilisateur = $em->getRepository(Commande::class)->findBy(['user' => $utilisateur]);
 
        // 8. Variables pour les administrateurs (initialisées à null)
        $benefices = null;
        $toutesCommandes = null;
 
        // 9. Si l'utilisateur est administrateur, récupérer des données supplémentaires
        if ($this->isGranted('ROLE_ADMIN')) {
            // Récupérer les bénéfices par jour (statistiques)
            $benefices = $em->getRepository(Commande::class)->getBeneficesParJour();
 
            // Récupérer toutes les commandes de tous les clients
            $toutesCommandes = $em->getRepository(Commande::class)->findAll();
        }
 
        // 10. Afficher la page de profil
        return $this->render('profil/index.html.twig', [
            'user' => $utilisateur,
            'form' => $formulaire->createView(),
            'commandes' => $commandesUtilisateur,
            'benefices' => $benefices,
            'commandesClients' => $toutesCommandes,
        ]);
    }
}
 
 