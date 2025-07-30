<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Commande;

final class ProfilController extends AbstractController
{
    /**
     *  PAGE PROFIL (basique)
     * Lien accessible via : /profil
     */
    #[Route('/profil', name: 'profil')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
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
            'commandes' => $commandes,
            'benefices' => $benefices,
            'commandesClients' => $commandesClients,
        ]);
    }
}