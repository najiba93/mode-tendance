<?php

//  Ceci est le dossier de ton contrôleur
namespace App\Controller;

//  On utilise l'entité "Produit" qui représente un produit dans ta base de données
use App\Entity\Produit;

//  On utilise le formulaire "ProduitType" qui contient les champs du produit
use App\Form\ProduitType;

//  C’est pour travailler avec la base de données
use Doctrine\ORM\EntityManagerInterface;

//  C’est le contrôleur de base que Symfony utilise
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

//  Pour gérer la requête (ce que l’utilisateur envoie dans le formulaire)
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//  Pour créer des routes (URLs)
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Categorie;
use App\Repository\ProduitRepository;


//  C’est ton contrôleur principal pour les produits
final class ProduitController extends AbstractController
{




#[Route('/Produits/categorie/{id}', name: 'produits_par_categorie')]
public function produitsParCategorie(Categorie $categorie, ProduitRepository $produitRepository): Response
{
    $produits = $produitRepository->findBy(['categorie' => $categorie]);

    return $this->render('Produits/index.html.twig', [
        'produits' => $produits,
        'categorie' => $categorie,
    ]);
}








        //  Cette route permet d’accéder à l’URL "/Produits"
    //  Elle affiche tous les produits enregistrés
    #[Route('/Produits', name: 'Produits')]
    public function index(EntityManagerInterface $em): Response
    {
        //  On récupère tous les produits dans la base
        $produits = $em->getRepository(Produit::class)->findAll();

        //  On affiche la page "Produits/index.html.twig" avec les produits
        return $this->render('Produits/index.html.twig', [
            'produits' => $produits, //  On envoie les produits à la page Twig
        ]);
    }


#[Route('/admin/produit/new', name: 'admin_produit_new')]
public function adminNew(Request $request, EntityManagerInterface $em): Response
{
    // 📦 On crée un nouveau produit vide
    $produit = new Produit();

    // 🧾 On construit le formulaire du produit
    $form = $this->createForm(ProduitType::class, $produit);

    // 📥 On vérifie si l'utilisateur a rempli et soumis le formulaire
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // 🖼️ On récupère l'image envoyée par l'utilisateur
        $imageFile = $form->get('imageFile')->getData();

        if ($imageFile) {
            // 🧠 On génère un nom de fichier unique comme "image_73642.jpg"
            $nomFichier = uniqid().'.'.$imageFile->guessExtension();

            // 🗂️ On déplace le fichier dans le dossier "public/uploads/"
            $imageFile->move('uploads/', $nomFichier);

            // 🖍️ On met le chemin de l'image dans l'objet Produit
            $produit->setImage('uploads/'.$nomFichier);
        } else {
            // 🎨 Si aucune image n'est envoyée, on met une image par défaut
            $produit->setImage('https://via.placeholder.com/400x300?text=Image+par+défaut');
        }

        // 💾 On enregistre le produit dans la base de données
        $em->persist($produit);
        $em->flush();

        // 🔁 On redirige vers la liste des produits
        return $this->redirectToRoute('Produits');
    }

    // 🖥️ Si le formulaire n'est pas encore envoyé, on l'affiche
    return $this->render('admin/produit_new.html.twig', [
        'form' => $form->createView(),
    ]);
}


#[Route('/produits/{id}', name: 'produits_carte')]
public function show(Produit $produit): Response
{
    return $this->render('produits/carte.html.twig', [
        'produit' => $produit,
    ]);
}









}
