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
 
/**
 * Contrôleur pour gérer les produits
 * Ce fichier gère tout ce qui concerne les produits : affichage, création, modification, suppression
 */
final class ProduitController extends AbstractController
{
    /**
     * AFFICHER TOUS LES PRODUITS
     * Route : /Produits
     * Méthode : GET
     */
    #[Route('/Produits', name: 'Produits')]
    public function index(EntityManagerInterface $em): Response
    {
        //  Récupérer tous les produits depuis la base de données
        $produits = $em->getRepository(Produit::class)->findAll();
 
        //  Afficher la page avec tous les produits
        return $this->render('Produits/index.html.twig', [
            'produits' => $produits,
        ]);
    }
 
    /**
     * AFFICHER LES PRODUITS D'UNE CATÉGORIE
     * Route : /Produits/categorie/{id}
     * Méthode : GET
     */
    #[Route('/Produits/categorie/{id}', name: 'produits_par_categorie')]
    public function produitsParCategorie(Categorie $categorie, ProduitRepository $produitRepository): Response
    {
        //  Récupérer tous les produits de cette catégorie
        $produits = $produitRepository->findBy(['categorie' => $categorie]);
 
        //  Afficher la page avec les produits filtrés
        return $this->render('Produits/index.html.twig', [
            'produits' => $produits,
            'categorie' => $categorie,
        ]);
    }
 
    /**
     * CRÉER UN NOUVEAU PRODUIT (ADMIN)
     * Route : /admin/produit/new
     * Méthode : GET et POST
     * Accès : ROLE_ADMIN uniquement
     */
    #[Route('/admin/produit/new', name: 'admin_produit_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminNew(Request $request, EntityManagerInterface $em): Response
    {
        //  Créer un nouveau produit vide
        $produit = new Produit();
 
        //  Créer le formulaire pour ce produit
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
 
        //  Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            //  Récupérer les images uploadées
            $imagesUploades = $form->get('images')->getData();
 
            //  Récupérer les couleurs (format texte)
            $couleursTexte = $form->get('couleurs')->getData();
 
            //  Traiter les couleurs (convertir le texte en tableau)
            if ($couleursTexte) {
                // Diviser le texte par virgules et nettoyer
                $couleurs = array_map('trim', explode(',', $couleursTexte));
                $couleurs = array_filter($couleurs); // Supprimer les éléments vides
                $produit->setCouleurs($couleurs);
            }
 
            //  Traiter les images uploadées
            if ($imagesUploades) {
                foreach ($imagesUploades as $imageFile) {
                    // Vérifier que l'image est valide
                    if ($this->verifierImage($imageFile)) {
                        // Uploader l'image et créer l'objet ImageProduit
                        $imageProduit = $this->uploaderImage($imageFile, $produit);
                        if ($imageProduit) {
                            $produit->addImage($imageProduit);
                        }
                    }
                }
            }
 
            //  Si aucune image n'a été uploadée, ajouter une image par défaut
            if ($produit->getImages()->isEmpty()) {
                $imageDefaut = new ImageProduit();
                $imageDefaut->setUrl('https://via.placeholder.com/400x300?text=Image+par+défaut');
                $imageDefaut->setProduit($produit);
                $produit->addImage($imageDefaut);
            }
 
            //  Sauvegarder le produit dans la base de données
            $em->persist($produit);
            $em->flush();
 
            //  Afficher un message de succès et rediriger
            $this->addFlash('success', 'Produit créé avec succès !');
            return $this->redirectToRoute('Produits');
        }
 
        //  Afficher le formulaire de création
        return $this->render('admin/produit_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
 
    /**
     * MODIFIER UN PRODUIT EXISTANT (ADMIN)
     * Route : /produits/{id}/modifier
     * Méthode : GET et POST
     * Accès : ROLE_ADMIN uniquement
     */
    #[Route('/produits/{id}/modifier', name: 'produit_modifier')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        //  Créer le formulaire avec les données du produit existant
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
 
        //  Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            //  Récupérer les nouvelles images
            $nouvellesImages = $form->get('images')->getData();

            //  Récupérer les couleurs
            $couleursTexte = $form->get('couleurs')->getData();
 
            //  Traiter les couleurs
            if ($couleursTexte) {
                $couleurs = array_map('trim', explode(',', $couleursTexte));
                $couleurs = array_filter($couleurs);
                $produit->setCouleurs($couleurs);
            }
 
            //  Si de nouvelles images sont fournies
            if ($nouvellesImages) {
                // Supprimer toutes les anciennes images
                foreach ($produit->getImages() as $ancienneImage) {
                    $this->supprimerFichierImage($ancienneImage->getUrl());
                    $em->remove($ancienneImage);
                }
                $produit->getImages()->clear();
 
                // Ajouter les nouvelles images
                foreach ($nouvellesImages as $imageFile) {
                    if ($this->verifierImage($imageFile)) {
                        $imageProduit = $this->uploaderImage($imageFile, $produit);
                        if ($imageProduit) {
                            $produit->addImage($imageProduit);
                        }
                    }
                }
            }
 
            //  Sauvegarder les modifications
            $em->flush();
            $this->addFlash('success', 'Produit modifié avec succès !');
 
            //  Rediriger vers la page du produit
            return $this->redirectToRoute('produits_carte', ['id' => $produit->getId()]);
        }
 
        //  Préparer les couleurs pour l'affichage dans le formulaire
        $couleursTexte = '';
        if ($produit->getCouleurs()) {
            $couleursTexte = implode(', ', $produit->getCouleurs());
        }
 
        // 10. Afficher le formulaire de modification
        return $this->render('admin/produit_modifier.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
            'couleursTexte' => $couleursTexte,
        ]);
    }
 
    /**
     * AFFICHER UN PRODUIT SPÉCIFIQUE
     * Route : /produits/{id}
     * Méthode : GET
     */
    #[Route('/produits/{id}', name: 'produits_carte')]
    public function show(Produit $produit): Response
    {
        // Afficher la page détaillée du produit
        return $this->render('produits/carte.html.twig', [
            'produit' => $produit,
        ]);
    }
 
    /**
     * SUPPRIMER UN PRODUIT (ADMIN)
     * Route : /produits/{id}/supprimer
     * Méthode : POST
     * Accès : ROLE_ADMIN uniquement
     */
    #[Route('/produits/{id}/supprimer', name: 'produit_supprimer', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function supprimer(Produit $produit, EntityManagerInterface $em): Response
    {
        // 1. Supprimer tous les fichiers d'images du produit
        foreach ($produit->getImages() as $image) {
            $this->supprimerFichierImage($image->getUrl());
        }
 
        // 2. Supprimer le produit de la base de données
        $em->remove($produit);
        $em->flush();
 
        // 3. Afficher un message de confirmation
        $this->addFlash('success', 'Produit supprimé avec succès !');
        return $this->redirectToRoute('Produits');
    }
 
    /**
     * SUPPRIMER UNE IMAGE SPÉCIFIQUE (ADMIN)
     * Route : /admin/image/{id}/supprimer
     * Méthode : POST
     * Accès : ROLE_ADMIN uniquement
     */
    #[Route('/admin/image/{id}/supprimer', name: 'admin_image_supprimer', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function supprimerImage(ImageProduit $image, EntityManagerInterface $em): Response
    {
        // 1. Récupérer l'ID du produit avant de supprimer l'image
        $produitId = $image->getProduit()->getId();
 
        // 2. Supprimer le fichier image du serveur
        $this->supprimerFichierImage($image->getUrl());
 
        // 3. Supprimer l'image de la base de données
        $em->remove($image);
        $em->flush();
 
        // 4. Afficher un message de confirmation
        $this->addFlash('success', 'Image supprimée avec succès !');
        return $this->redirectToRoute('produit_modifier', ['id' => $produitId]);
    }
 
    /**
     * VÉRIFIER SI UN FICHIER IMAGE EST VALIDE
     * Méthode privée utilisée en interne
     */
    private function verifierImage(UploadedFile $fichier): bool
    {
        // Types de fichiers autorisés
        $typesAutorises = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
 
        // Taille maximale (5MB)
        $tailleMaximale = 5 * 1024 * 1024;
 
        // Vérifier le type de fichier
        if (!in_array($fichier->getMimeType(), $typesAutorises)) {
            $this->addFlash('error', 'Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.');
            return false;
        }
 
        // Vérifier la taille du fichier
        if ($fichier->getSize() > $tailleMaximale) {
            $this->addFlash('error', 'Le fichier est trop volumineux. Taille maximale : 5MB.');
            return false;
        }
 
        return true;
    }
 
    /**
     * UPLOADER UNE IMAGE ET CRÉER L'OBJET IMAGEPRODUIT
     * Méthode privée utilisée en interne
     */
    private function uploaderImage(UploadedFile $fichierImage, Produit $produit): ?ImageProduit
    {
        // 1. Générer un nom de fichier unique
        $nomFichier = uniqid() . '.' . $fichierImage->guessExtension();
 
        // 2. Définir le dossier d'upload
        $dossierUpload = $this->getParameter('kernel.project_dir') . '/public/uploads/produits/';
 
        // 3. Créer le dossier s'il n'existe pas
        if (!is_dir($dossierUpload)) {
            mkdir($dossierUpload, 0755, true);
        }
 
        try {
            // 4. Déplacer le fichier uploadé
            $fichierImage->move($dossierUpload, $nomFichier);
 
            // 5. Créer l'objet ImageProduit
            $imageProduit = new ImageProduit();
            $imageProduit->setUrl('/uploads/produits/' . $nomFichier);
            $imageProduit->setProduit($produit);
 
            return $imageProduit;
        } catch (FileException $e) {
            // 6. En cas d'erreur, afficher un message
            $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
            return null;
        }
    }
 
    /**
     * SUPPRIMER UN FICHIER IMAGE DU SERVEUR
     * Méthode privée utilisée en interne
     */
    private function supprimerFichierImage(string $urlImage): void
    {
        // Ne pas supprimer les images placeholder ou les URLs externes
        if (strpos($urlImage, 'placeholder') !== false || strpos($urlImage, 'http') === 0) {
            return;
        }
 
        // Construire le chemin complet du fichier
        $cheminFichier = $this->getParameter('kernel.project_dir') . '/public' . $urlImage;
 
        // Supprimer le fichier s'il existe
        if (file_exists($cheminFichier)) {
            unlink($cheminFichier);
        }
    }
}