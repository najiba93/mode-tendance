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
 * ========================================
 * CONTRÔLEUR DE GESTION DES PRODUITS
 * ========================================
 * 
 * Ce contrôleur gère tout ce qui concerne les produits :
 * - Affichage des produits (public et admin)
 * - Création de nouveaux produits (admin)
 * - Modification des produits existants (admin)
 * - Suppression des produits (admin)
 * - Gestion des images et couleurs
 * 
 * @final Cette classe ne peut pas être étendue
 */
final class ProduitController extends AbstractController
{
    /**
     * ========================================
     * AFFICHAGE DE TOUS LES PRODUITS
     * ========================================
     * 
     * Route : /Produits
     * Méthode : GET
     * Accès : Public
     * 
     * Affiche la page principale des produits avec tous les produits disponibles
     * 
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @return Response Page des produits rendue
     */
    #[Route('/Produits', name: 'Produits')]
    public function index(EntityManagerInterface $em): Response
    {
        // ========================================
        // RÉCUPÉRATION DES DONNÉES
        // ========================================
        
        // Récupère tous les produits depuis la base de données
        // Utilise le repository Doctrine pour optimiser les requêtes
        $produits = $em->getRepository(Produit::class)->findAll();
 
        // ========================================
        // RENDU DE LA PAGE
        // ========================================
        
        // Affiche la page avec tous les produits
        // Le template 'Produits/index.html.twig' sera utilisé
        return $this->render('Produits/index.html.twig', [
            'produits' => $produits, // Passé au template pour affichage
        ]);
    }
 
    /**
     * ========================================
     * AFFICHAGE DES PRODUITS D'UNE CATÉGORIE
     * ========================================
     * 
     * Route : /Produits/categorie/{id}
     * Méthode : GET
     * Accès : Public
     * 
     * Affiche les produits filtrés par catégorie
     * Utilise le paramètre converter de Symfony pour récupérer automatiquement la catégorie
     * 
     * @param Categorie $categorie Catégorie sélectionnée (récupérée automatiquement)
     * @param ProduitRepository $produitRepository Repository des produits
     * @return Response Page des produits filtrés rendue
     */
    #[Route('/Produits/categorie/{id}', name: 'produits_par_categorie')]
    public function produitsParCategorie(Categorie $categorie, ProduitRepository $produitRepository): Response
    {
        // ========================================
        // RÉCUPÉRATION DES DONNÉES FILTRÉES
        // ========================================
        
        // Récupère tous les produits de cette catégorie spécifique
        // Utilise le repository pour une requête optimisée
        $produits = $produitRepository->findBy(['categorie' => $categorie]);
 
        // ========================================
        // RENDU DE LA PAGE
        // ========================================
        
        // Affiche la page avec les produits filtrés par catégorie
        // Passe aussi la catégorie pour l'affichage du titre
        return $this->render('Produits/index.html.twig', [
            'produits' => $produits, // Produits de la catégorie
            'categorie' => $categorie, // Catégorie sélectionnée
        ]);
    }
 
    /**
     * ========================================
     * CRÉATION D'UN NOUVEAU PRODUIT (ADMIN)
     * ========================================
     * 
     * Route : /admin/produit/new
     * Méthode : GET et POST
     * Accès : ROLE_ADMIN uniquement
     * 
     * Permet aux administrateurs de créer de nouveaux produits
     * Gère l'upload d'images et la conversion des couleurs
     * 
     * @param Request $request Requête HTTP (formulaire)
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @return Response Page de création ou redirection
     */
    #[Route('/admin/produit/new', name: 'admin_produit_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminNew(Request $request, EntityManagerInterface $em): Response
    {
        // ========================================
        // CRÉATION DU PRODUIT ET DU FORMULAIRE
        // ========================================
        
        // Crée un nouveau produit vide
        $produit = new Produit();
 
        // Crée le formulaire pour ce produit
        // Utilise ProduitType qui définit tous les champs nécessaires
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
 
        // ========================================
        // TRAITEMENT DU FORMULAIRE SOUMIS
        // ========================================
        
        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            
            // ========================================
            // GESTION DES COULEURS
            // ========================================
            
            // Récupère les couleurs (format texte séparé par des virgules)
            $couleursTexte = $form->get('couleurs')->getData();
 
            // Traite les couleurs (convertit le texte en tableau)
            if ($couleursTexte) {
                // Divise le texte par virgules et nettoie chaque couleur
                $couleurs = array_map('trim', explode(',', $couleursTexte));
                $couleurs = array_filter($couleurs); // Supprime les éléments vides
                $produit->setCouleurs($couleurs);
            }
 
            // ========================================
            // GESTION DES IMAGES
            // ========================================
            
            // Récupère les images uploadées
            $imagesUploades = $form->get('images')->getData();
 
            // Traite les images uploadées
            if ($imagesUploades) {
                foreach ($imagesUploades as $imageFile) {
                    // Vérifie que l'image est valide
                    if ($this->verifierImage($imageFile)) {
                        // Upload l'image et crée l'objet ImageProduit
                        $imageProduit = $this->uploaderImage($imageFile, $produit);
                        if ($imageProduit) {
                            $produit->addImage($imageProduit);
                        }
                    }
                }
            }
 
            // ========================================
            // GESTION DES IMAGES PAR DÉFAUT
            // ========================================
            
            // Si aucune image n'a été uploadée, ajoute une image par défaut
            if ($produit->getImages()->isEmpty()) {
                $imageDefaut = new ImageProduit();
                $imageDefaut->setUrl('https://via.placeholder.com/400x300?text=Image+par+défaut');
                $imageDefaut->setProduit($produit);
                $produit->addImage($imageDefaut);
            }
 
            // ========================================
            // SAUVEGARDE DU PRODUIT
            // ========================================
            
            // Sauvegarde le produit dans la base de données
            $em->persist($produit);
            $em->flush();
 
            // ========================================
            // REDIRECTION ET MESSAGE DE SUCCÈS
            // ========================================
            
            // Affiche un message de succès et redirige vers la liste des produits
            $this->addFlash('success', 'Produit créé avec succès !');
            return $this->redirectToRoute('Produits');
        }
 
        // ========================================
        // AFFICHAGE DU FORMULAIRE DE CRÉATION
        // ========================================
        
        // Affiche le formulaire de création
        return $this->render('admin/produit_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
 
    /**
     * ========================================
     * MODIFICATION D'UN PRODUIT EXISTANT (ADMIN)
     * ========================================
     * 
     * Route : /produits/{id}/modifier
     * Méthode : GET et POST
     * Accès : ROLE_ADMIN uniquement
     * 
     * Permet aux administrateurs de modifier les produits existants
     * Gère l'upload d'images et la conversion des couleurs
     * 
     * @param Request $request Requête HTTP (formulaire)
     * @param Produit $produit Produit à modifier (récupéré automatiquement)
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @return Response Page de modification ou redirection
     */
    #[Route('/produits/{id}/modifier', name: 'produit_modifier')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $em): Response
    {
        // ========================================
        // CRÉATION DU FORMULAIRE ET RÉCUPÉRATION DES DONNÉES
        // ========================================
        
        // Crée le formulaire avec les données actuelles du produit
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
 
        // ========================================
        // TRAITEMENT DU FORMULAIRE SOUMIS
        // ========================================
        
        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            
            // ========================================
            // GESTION DES IMAGES
            // ========================================
            
            // Récupère les nouvelles images uploadées
            $nouvellesImages = $form->get('images')->getData();
 
            // ========================================
            // GESTION DES COULEURS
            // ========================================
            
            // Récupère les couleurs (format texte séparé par des virgules)
            $couleursTexte = $form->get('couleurs')->getData();
 
            // Traite les couleurs
            if ($couleursTexte) {
                $couleurs = array_map('trim', explode(',', $couleursTexte));
                $couleurs = array_filter($couleurs);
                $produit->setCouleurs($couleurs);
            }
 
            // ========================================
            // GESTION DES IMAGES UPDATED
            // ========================================
            
            // Si de nouvelles images sont fournies
            if ($nouvellesImages) {
                // Supprime toutes les anciennes images du produit
                foreach ($produit->getImages() as $ancienneImage) {
                    $this->supprimerFichierImage($ancienneImage->getUrl());
                    $em->remove($ancienneImage);
                }
                $produit->getImages()->clear();
 
                // Ajoute les nouvelles images
                foreach ($nouvellesImages as $imageFile) {
                    if ($this->verifierImage($imageFile)) {
                        $imageProduit = $this->uploaderImage($imageFile, $produit);
                        if ($imageProduit) {
                            $produit->addImage($imageProduit);
                        }
                    }
                }
            }
 
            // ========================================
            // SAUVEGARDE DES MODIFICATIONS
            // ========================================
            
            // Sauvegarde les modifications dans la base de données
            $em->flush();
            $this->addFlash('success', 'Produit modifié avec succès !');
 
            // ========================================
            // REDIRECTION VERS LA PAGE DU PRODUIT
            // ========================================
            
            // Redirige vers la page de détail du produit
            return $this->redirectToRoute('produits_carte', ['id' => $produit->getId()]);
        }
 
        // ========================================
        // PRÉPARATION DES COULEURS POUR LE FORMULAIRE
        // ========================================
        
        // Prépare le texte des couleurs pour l'affichage dans le formulaire
        $couleursTexte = '';
        if ($produit->getCouleurs()) {
            $couleursTexte = implode(', ', $produit->getCouleurs());
        }
 
        // ========================================
        // AFFICHAGE DU FORMULAIRE DE MODIFICATION
        // ========================================
        
        // Affiche le formulaire de modification
        return $this->render('admin/produit_modifier.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
            'couleursTexte' => $couleursTexte,
        ]);
    }
 
    /**
     * ========================================
     * AFFICHAGE D'UN PRODUIT SPÉCIFIQUE
     * ========================================
     * 
     * Route : /produits/{id}
     * Méthode : GET
     * Accès : Public
     * 
     * Affiche la page détaillée d'un produit
     * 
     * @param Produit $produit Produit à afficher (récupéré automatiquement)
     * @return Response Page détaillée du produit rendue
     */
    #[Route('/produits/{id}', name: 'produits_carte')]
    public function show(Produit $produit): Response
    {
        // ========================================
        // AFFICHAGE DE LA PAGE DÉTAILLÉE
        // ========================================
        
        // Affiche la page détaillée du produit
        return $this->render('produits/carte.html.twig', [
            'produit' => $produit,
        ]);
    }
 
    /**
     * ========================================
     * SUPPRESSION D'UN PRODUIT (ADMIN)
     * ========================================
     * 
     * Route : /produits/{id}/supprimer
     * Méthode : POST
     * Accès : ROLE_ADMIN uniquement
     * 
     * Permet aux administrateurs de supprimer un produit
     * Supprime toutes les images associées
     * 
     * @param Produit $produit Produit à supprimer (récupéré automatiquement)
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @return Response Redirection vers la liste des produits
     */
    #[Route('/produits/{id}/supprimer', name: 'produit_supprimer', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function supprimer(Produit $produit, EntityManagerInterface $em): Response
    {
        // ========================================
        // SUPPRESSION DES FICHIERS D'IMAGES
        // ========================================
        
        // Supprime tous les fichiers d'images du produit
        foreach ($produit->getImages() as $image) {
            $this->supprimerFichierImage($image->getUrl());
        }
 
        // ========================================
        // SUPPRESSION DU PRODUIT
        // ========================================
        
        // Supprime le produit de la base de données
        $em->remove($produit);
        $em->flush();
 
        // ========================================
        // MESSAGE DE CONFIRMATION
        // ========================================
        
        // Affiche un message de confirmation
        $this->addFlash('success', 'Produit supprimé avec succès !');
        return $this->redirectToRoute('Produits');
    }
 
    /**
     * ========================================
     * SUPPRESSION D'UNE IMAGE SPÉCIFIQUE (ADMIN)
     * ========================================
     * 
     * Route : /admin/image/{id}/supprimer
     * Méthode : POST
     * Accès : ROLE_ADMIN uniquement
     * 
     * Permet aux administrateurs de supprimer une image spécifique d'un produit
     * Supprime le fichier image et l'image de la base de données
     * 
     * @param ImageProduit $image Image à supprimer (récupérée automatiquement)
     * @param EntityManagerInterface $em Gestionnaire d'entités Doctrine
     * @return Response Redirection vers la page de modification du produit
     */
    #[Route('/admin/image/{id}/supprimer', name: 'admin_image_supprimer', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function supprimerImage(ImageProduit $image, EntityManagerInterface $em): Response
    {
        // ========================================
        // RÉCUPÉRATION DE L'ID DU PRODUIT
        // ========================================
        
        // Récupère l'ID du produit avant de supprimer l'image
        $produitId = $image->getProduit()->getId();
 
        // ========================================
        // SUPPRESSION DU FICHIER IMAGE
        // ========================================
        
        // Supprime le fichier image du serveur
        $this->supprimerFichierImage($image->getUrl());
 
        // ========================================
        // SUPPRESSION DE L'IMAGE
        // ========================================
        
        // Supprime l'image de la base de données
        $em->remove($image);
        $em->flush();
 
        // ========================================
        // MESSAGE DE CONFIRMATION
        // ========================================
        
        // Affiche un message de confirmation
        $this->addFlash('success', 'Image supprimée avec succès !');
        return $this->redirectToRoute('produit_modifier', ['id' => $produitId]);
    }
 
    /**
     * ========================================
     * VÉRIFICATION D'UN FICHIER IMAGE
     * ========================================
     * 
     * Méthode privée utilisée en interne
     * Vérifie si un fichier est une image valide et respecte les limites de taille
     * 
     * @param UploadedFile $fichier Fichier à vérifier
     * @return bool True si le fichier est valide, False sinon
     */
    private function verifierImage(UploadedFile $fichier): bool
    {
        // ========================================
        // DÉFINITION DES TYPES D'IMAGES AUTORISÉS
        // ========================================
        
        // Types de fichiers autorisés (ex: image/jpeg, image/png, image/gif, image/webp)
        $typesAutorises = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
 
        // ========================================
        // TAILLE MAXIMALE DE L'IMAGE
        // ========================================
        
        // Taille maximale autorisée (5MB)
        $tailleMaximale = 5 * 1024 * 1024;
 
        // ========================================
        // VÉRIFICATION DU TYPE DE FICHIER
        // ========================================
        
        // Vérifie si le type de fichier est autorisé
        if (!in_array($fichier->getMimeType(), $typesAutorises)) {
            $this->addFlash('error', 'Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.');
            return false;
        }
 
        // ========================================
        // VÉRIFICATION DE LA TAILLE DU FICHIER
        // ========================================
        
        // Vérifie si la taille du fichier est inférieure à la taille maximale
        if ($fichier->getSize() > $tailleMaximale) {
            $this->addFlash('error', 'Le fichier est trop volumineux. Taille maximale : 5MB.');
            return false;
        }
 
        // ========================================
        // RETOUR DE LA VALIDATION
        // ========================================
        
        return true;
    }
 
    /**
     * ========================================
     * UPLOAD D'UNE IMAGE ET CRÉATION DE L'OBJET IMAGEPRODUIT
     * ========================================
     * 
     * Méthode privée utilisée en interne
     * Upload l'image sur le serveur et crée l'objet ImageProduit
     * 
     * @param UploadedFile $fichierImage Fichier image à uploader
     * @param Produit $produit Produit auquel l'image est associée
     * @return ImageProduit Objet ImageProduit créé ou null en cas d'erreur
     */
    private function uploaderImage(UploadedFile $fichierImage, Produit $produit): ?ImageProduit
    {
        // ========================================
        // GÉNÉRATION DU NOM DE FICHIER UNIQUE
        // ========================================
        
        // Génère un nom de fichier unique pour éviter les conflits
        $nomFichier = uniqid() . '.' . $fichierImage->guessExtension();
 
        // ========================================
        // DÉFINITION DU DOSSIER D'UPLOAD
        // ========================================
        
        // Définit le dossier où l'image sera uploadée
        $dossierUpload = $this->getParameter('kernel.project_dir') . '/public/uploads/produits/';
 
        // ========================================
        // CRÉATION DU DOSSIER SI NÉCESSAIRE
        // ========================================
        
        // Crée le dossier s'il n'existe pas
        if (!is_dir($dossierUpload)) {
            mkdir($dossierUpload, 0755, true);
        }
 
        try {
            // ========================================
            // DÉPLACEMENT DU FICHIER UPLOADÉ
            // ========================================
            
            // Déplace le fichier uploadé vers le dossier d'upload
            $fichierImage->move($dossierUpload, $nomFichier);
 
            // ========================================
            // CRÉATION DE L'OBJET IMAGEPRODUIT
            // ========================================
            
            // Crée l'objet ImageProduit
            $imageProduit = new ImageProduit();
            $imageProduit->setUrl('/uploads/produits/' . $nomFichier);
            $imageProduit->setProduit($produit);
 
            // ========================================
            // RETOUR DE L'OBJET IMAGEPRODUIT
            // ========================================
            
            return $imageProduit;
        } catch (FileException $e) {
            // ========================================
            // GESTION DES ERREURS D'UPLOAD
            // ========================================
            
            // En cas d'erreur, affiche un message d'erreur
            $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
            return null;
        }
    }
 
    /**
     * ========================================
     * SUPPRESSION D'UN FICHIER IMAGE DU SERVEUR
     * ========================================
     * 
     * Méthode privée utilisée en interne
     * Supprime le fichier image du serveur si ce n'est pas une image par défaut ou une URL externe
     * 
     * @param string $urlImage URL de l'image à supprimer
     */
    private function supprimerFichierImage(string $urlImage): void
    {
        // ========================================
        // VÉRIFICATION DE L'URL
        // ========================================
        
        // Ne supprime pas les images placeholder ou les URLs externes
        if (strpos($urlImage, 'placeholder') !== false || strpos($urlImage, 'http') === 0) {
            return;
        }
 
        // ========================================
        // CONSTRUCTION DU CHEMIN DU FICHIER
        // ========================================
        
        // Construit le chemin complet du fichier à supprimer
        $cheminFichier = $this->getParameter('kernel.project_dir') . '/public' . $urlImage;
 
        // ========================================
        // SUPPRESSION DU FICHIER
        // ========================================
        
        // Supprime le fichier s'il existe
        if (file_exists($cheminFichier)) {
            unlink($cheminFichier);
        }
    }
}