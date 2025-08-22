<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(EntityManagerInterface $em, CommandeRepository $commandeRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->getUser();

        $mesCommandes = $em->getRepository(Commande::class)->findBy(['user' => $user], ['date' => 'DESC']);
        $commandesClients = $em->getRepository(Commande::class)->findBy([], ['date' => 'DESC']);
        $benefices = $commandeRepository->getBeneficesParJour();
        $mesArticles = $em->getRepository(Produit::class)->findBy([], ['id' => 'DESC']);

        return $this->render('admin/index.html.twig', [
            'mesCommandes' => $mesCommandes,
            'commandesClients' => $commandesClients,
            'benefices' => $benefices,
            'mesArticles' => $mesArticles,
        ]);
    }
} 