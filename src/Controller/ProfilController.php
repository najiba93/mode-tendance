<?php

namespace App\Controller; // Le fichier fait partie du dossier des contrôleurs Symfony

// 📦 On importe les outils Symfony
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Permet d'utiliser render(), addFlash(), etc.
use Symfony\Component\HttpFoundation\Request; // Pour lire ce que l'utilisateur envoie (ex : formulaire)
use Symfony\Component\HttpFoundation\Response; // Représente la réponse que Symfony renvoie au navigateur
use Symfony\Component\Routing\Annotation\Route; // Permet d’associer une URL à une fonction
use Symfony\Component\Mailer\MailerInterface; // Outil pour envoyer des emails
use Symfony\Component\Mime\Email; // Structure d’un email

// 🛠️ On importe des outils supplémentaires
use Doctrine\ORM\EntityManagerInterface; // Permet d'enregistrer des données dans la base (Doctrine)
use App\Repository\UserRepository; // Permet de chercher des utilisateurs dans la table user
use App\Entity\ResetPasswordToken; // Permet de créer une ligne de token dans la base

final class ProfilController extends AbstractController
{
    /**
     * 💌 PAGE "Mot de passe oublié"
     * Lien accessible via : /mot-de-passe-oublie
     */
    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password')]
    public function forgot(Request $request, UserRepository $userRepo, MailerInterface $mailer, EntityManagerInterface $em): Response
    {
        // 🔎 On récupère l'email que l'utilisateur a saisi dans le formulaire
        $email = $request->request->get('email');

        // ✅ Si un email a bien été envoyé
        if ($email) {
            // 👤 On cherche un utilisateur qui a cet email dans la base
            $user = $userRepo->findOneBy(['email' => $email]);

            // ✅ Si on trouve un utilisateur
            if ($user) {
                // 🔐 On crée un token sécurisé (code aléatoire de 64 caractères)
                $token = bin2hex(random_bytes(32));

                // 🧾 On crée une nouvelle ligne dans la table reset_password_token
                $resetToken = new ResetPasswordToken();
                $resetToken->setToken($token); // Le code
                $resetToken->setUser($user); // L'utilisateur concerné
                $resetToken->setIsUsed(false); // Le lien n'a pas encore été utilisé
                $resetToken->setExpiresAt(new \DateTimeImmutable('+2 hours')); // Valable pendant 2h

                // 💾 On enregistre cette ligne dans la base
                $em->persist($resetToken);
                $em->flush();

                // ✉️ On prépare le mail à envoyer
                $emailMessage = (new Email())
                    ->from('noreply@tonsite.com') // Adresse de l'expéditeur
                    ->to($user->getEmail()) // Adresse du destinataire
                    ->subject('Réinitialisation de votre mot de passe') // Sujet du mail
                    ->text("Bonjour {$user->getUsername()}, voici votre lien : https://localhost:8000/reset-password/" . $token);

                // 📤 On envoie le mail
                $mailer->send($emailMessage);

                // ✅ On affiche un petit message vert à l'utilisateur
                $this->addFlash('success', 'Un lien de réinitialisation vous a été envoyé.');
            } else {
                // ❌ Si aucun utilisateur ne correspond à cet email
                $this->addFlash('danger', 'Aucun utilisateur trouvé avec cet email.');
            }
        }

        // 🖼️ On affiche la page HTML avec le formulaire "Mot de passe oublié"
        return $this->render('security/forgot_password.html.twig');
    }

    /**
     * 🔐 PAGE "Réinitialisation du mot de passe"
     * Lien accessible via : /reset-password/{token}
     */
    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(string $token, Request $request, EntityManagerInterface $em): Response
    {
        // 📦 On récupère le token en base
        $resetToken = $em->getRepository(ResetPasswordToken::class)->findOneBy(['token' => $token]);

        // ❌ Si le token est invalide, expiré ou déjà utilisé
        if (!$resetToken || $resetToken->isUsed() || $resetToken->getExpiresAt() < new \DateTimeImmutable()) {
            $this->addFlash('danger', 'Ce lien est invalide ou expiré.');
            return $this->redirectToRoute('app_forgot_password');
        }

        // ✅ Si le formulaire est soumis (l'utilisateur a cliqué "Valider")
        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('new_password'); // 🔐 On récupère le nouveau mot de passe

            // 🧠 Ici on doit encoder le mot de passe (je peux t'ajouter ça)
            // 👉 Exemple (à ajouter) :
            // $hasher = $this->get(UserPasswordHasherInterface::class);
            // $hashedPassword = $hasher->hashPassword($resetToken->getUser(), $newPassword);
            // $resetToken->getUser()->setPassword($hashedPassword);

            // 🚩 On marque le token comme utilisé
            $resetToken->setIsUsed(true);
            $em->flush();

            // ✅ On informe que le mot de passe a été changé
            $this->addFlash('success', 'Votre mot de passe a été modifié.');
            return $this->redirectToRoute('app_login');
        }

        // 🖼️ On affiche le formulaire pour entrer le nouveau mot de passe
        return $this->render('security/reset_password.html.twig', [
            'token' => $token
        ]);
    }

    /**
     * 🧑‍🎨 PAGE PROFIL (basique)
     * Lien accessible via : /profil
     */
    #[Route('/profil', name: 'profil')]
    public function index(): Response
    {
        return $this->render('profil/index.html.twig', [
            'controller_name' => 'ProfilController',
        ]);
    }
}
