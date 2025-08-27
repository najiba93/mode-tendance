<?php
 
namespace App\Controller;
 
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
 
/**
 * Contrôleur pour gérer l'inscription des utilisateurs
 * Ce fichier gère tout ce qui concerne l'inscription : création de compte, validation
 */
class RegistrationController extends AbstractController
{
    /**
     * PAGE D'INSCRIPTION
     * Route : /inscription
     * Méthode : GET et POST
     * Accès : Public (tout le monde peut s'inscrire)
     */
    #[Route('/inscription', name: 'inscription')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasheurMotDePasse,
        EntityManagerInterface $em
    ): Response {
        //  Créer un nouvel utilisateur vide
        $nouvelUtilisateur = new User();
 
        // Créer le formulaire d'inscription
        $formulaire = $this->createForm(RegistrationFormType::class, $nouvelUtilisateur);
        $formulaire->handleRequest($request);
 
        //  Si le formulaire est soumis et valide
        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            //  Récupérer le mot de passe en clair depuis le formulaire
            $motDePasseClair = $formulaire->get('plainPassword')->getData();
 
            //  Hasher (crypter) le mot de passe pour la sécurité
            $motDePasseHashe = $hasheurMotDePasse->hashPassword(
                $nouvelUtilisateur,
                $motDePasseClair
            );
 
            //  Définir le mot de passe hashé dans l'utilisateur
            $nouvelUtilisateur->setPassword($motDePasseHashe);
 
            //  Remplir les champs supplémentaires automatiquement
            // Si le nom de famille n'est pas défini, utiliser le nom complet
            $nouvelUtilisateur->setLastName($nouvelUtilisateur->getNom() ?? '');
 
            // Définir les dates de création et de modification
            $nouvelUtilisateur->setCreatedAt(new \DateTimeImmutable());
            $nouvelUtilisateur->setUpdatedAt(new \DateTimeImmutable());
 
            //  Sauvegarder le nouvel utilisateur dans la base de données
            $em->persist($nouvelUtilisateur);
            $em->flush();
 
            //  Afficher un message de succès
            $this->addFlash('success', 'Inscription réussie ✅');
 
            //  Rediriger vers la page de connexion
            return $this->redirectToRoute('app_login');
        }
 
        //  Afficher le formulaire d'inscription
        return $this->render('registration/index.html.twig', [
            'registrationForm' => $formulaire->createView(),
        ]);
    }
}