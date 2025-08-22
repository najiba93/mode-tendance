<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Categorie;
use App\Entity\ImageProduit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\CategorieRepository;
final class ProduitController extends AbstractController
{
    #[Route('/Produits', name: 'Produits')]
    public function index(EntityManagerInterface $em): Response
    {
        $produits = $em->getRepository(Produit::class)->findAll();

        return $this->render('Produits/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/Produits/categorie/{id}', name: 'produits_par_categorie')]
    public function produitsParCategorie(Categorie $categorie, ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findBy(['categorie' => $categorie]);

        return $this->render('Produits/index.html.twig', [
            'produits' => $produits,
            'categorie' => $categorie,
        ]);
    }

    #[Route('/admin/produit/new', name: 'admin_produit_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminNew(Request $request, EntityManagerInterface $em): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFiles = $form->get('images')->getData();
            $couleursTexte = $form->get('couleurs')->getData();

            // Traiter les couleurs
            if ($couleursTexte) {
                $couleurs = array_map('trim', explode(',', $couleursTexte));
                $couleurs = array_filter($couleurs); // Supprimer les éléments vides
                $produit->setCouleurs($couleurs);
            }

            if ($imageFiles) {
                foreach ($imageFiles as $imageFile) {
                    if ($this->isValidImageFile($imageFile)) {
                        $imageProduit = $this->handleImageUpload($imageFile, $produit);
                        if ($imageProduit) {
                            $produit->addImage($imageProduit);
                        }
                    }
                }
            }

            if ($produit->getImages()->isEmpty()) {
                $imageDefaut = new ImageProduit();
                $imageDefaut->setUrl('https://via.placeholder.com/400x300?text=Image+par+défaut');
                $imageDefaut->setProduit($produit);
                $produit->addImage($imageDefaut);
            }

            $em->persist($produit);
            $em->flush();

            $this->addFlash('success', 'Produit créé avec succès');
            return $this->redirectToRoute('Produits');
        }

        return $this->render('admin/produit_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/produits/{id}/modifier', name: 'produit_modifier')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('images')->getData();
            $couleursTexte = $form->get('couleurs')->getData();

            // Traiter les couleurs
            if ($couleursTexte) {
                $couleurs = array_map('trim', explode(',', $couleursTexte));
                $couleurs = array_filter($couleurs); // Supprimer les éléments vides
                $produit->setCouleurs($couleurs);
            }

            if ($images) {
                // Supprimer les anciennes images si de nouvelles sont fournies
                foreach ($produit->getImages() as $ancienneImage) {
                    $this->deleteImageFile($ancienneImage->getUrl());
                    $em->remove($ancienneImage);
                }
                $produit->getImages()->clear();

                // Ajouter les nouvelles images
                foreach ($images as $imageFile) {
                    if ($this->isValidImageFile($imageFile)) {
                        $imageProduit = $this->handleImageUpload($imageFile, $produit);
                        if ($imageProduit) {
                            $produit->addImage($imageProduit);
                        }
                    }
                }
            }

            $em->flush();
            $this->addFlash('success', 'Produit modifié avec succès');

            return $this->redirectToRoute('produits_carte', ['id' => $produit->getId()]);
        }

        // Préparer les couleurs pour l'affichage
        $couleursTexte = '';
        if ($produit->getCouleurs()) {
            $couleursTexte = implode(', ', $produit->getCouleurs());
        }

        return $this->render('admin/produit_modifier.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
            'couleursTexte' => $couleursTexte,
        ]);
    }

    #[Route('/produits/{id}', name: 'produits_carte')]
    public function show(Produit $produit): Response
    {
        return $this->render('produits/carte.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/produits/{id}/supprimer', name: 'produit_supprimer', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function supprimer(Produit $produit, EntityManagerInterface $em): Response
    {
        foreach ($produit->getImages() as $image) {
            $this->deleteImageFile($image->getUrl());
        }

        $em->remove($produit);
        $em->flush();

        $this->addFlash('success', 'Produit supprimé avec succès');
        return $this->redirectToRoute('Produits');
    }

    #[Route('/admin/image/{id}/supprimer', name: 'admin_image_supprimer', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function supprimerImage(ImageProduit $image, EntityManagerInterface $em): Response
    {
        $produitId = $image->getProduit()->getId();
        $this->deleteImageFile($image->getUrl());
        $em->remove($image);
        $em->flush();

        $this->addFlash('success', 'Image supprimée avec succès');
        return $this->redirectToRoute('produit_modifier', ['id' => $produitId]);
    }

    private function isValidImageFile(UploadedFile $file): bool
    {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            $this->addFlash('error', 'Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.');
            return false;
        }

        if ($file->getSize() > $maxSize) {
            $this->addFlash('error', 'Le fichier est trop volumineux. Taille maximale : 5MB.');
            return false;
        }

        return true;
    }

    private function handleImageUpload(UploadedFile $imageFile, Produit $produit): ?ImageProduit
    {
        $nomFichier = uniqid() . '.' . $imageFile->guessExtension();
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/produits/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        try {
            $imageFile->move($uploadDir, $nomFichier);

            $imageProduit = new ImageProduit();
            $imageProduit->setUrl('/uploads/produits/' . $nomFichier);
            $imageProduit->setProduit($produit);

            return $imageProduit;
        } catch (FileException $e) {
            $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
            return null;
        }
    }

    private function deleteImageFile(string $imageUrl): void
    {
        if (strpos($imageUrl, 'placeholder') !== false || strpos($imageUrl, 'http') === 0) {
            return;
        }

        $filePath = $this->getParameter('kernel.project_dir') . '/public' . $imageUrl;

        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }






    
}