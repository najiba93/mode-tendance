<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Commande;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;

final class ProfilController extends AbstractController
{
    /**
     *  PAGE PROFIL (basique)
     * Lien accessible via : /profil
     */
    #[Route('/profil', name: 'profil')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Informations modifiées avec succès.');
            return $this->redirectToRoute('profil');
        }

        $commandes = $em->getRepository(Commande::class)->findBy(['user' => $user]);

        $benefices = null;
        $commandesClients = null;
        if ($this->isGranted('ROLE_ADMIN')) {
            // Exemple : calcul des bénéfices par jour
            $benefices = $em->getRepository(Commande::class)->getBeneficesParJour();
            $commandesClients = $em->getRepository(Commande::class)->findAll();
        }

        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(), // <-- AJOUTE CETTE LIGNE
            'commandes' => $commandes,
            'benefices' => $benefices,
            'commandesClients' => $commandesClients,
        ]);
    }
}