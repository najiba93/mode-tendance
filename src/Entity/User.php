<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ========================================
 * ENTITÉ UTILISATEUR
 * ========================================
 * 
 * Cette entité représente un utilisateur de l'application.
 * Elle implémente les interfaces de sécurité Symfony pour l'authentification.
 * 
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // ========================================
    // PROPRIÉTÉS D'IDENTIFICATION
    // ========================================
    
    /**
     * Identifiant unique de l'utilisateur
     * Auto-généré par la base de données
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Email de connexion (unique)
     * Utilisé comme identifiant principal pour l'authentification
     */
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'email doit être valide.')]
    private ?string $email = null;

    // ========================================
    // PROPRIÉTÉS DE SÉCURITÉ
    // ========================================
    
    /**
     * Rôles de sécurité de l'utilisateur
     * Stockés en JSON pour permettre plusieurs rôles
     * Exemples : ROLE_USER, ROLE_ADMIN
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * Mot de passe hashé de l'utilisateur
     * Jamais stocké en clair pour des raisons de sécurité
     */
    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    // ========================================
    // INFORMATIONS PERSONNELLES
    // ========================================
    
    /**
     * Prénom de l'utilisateur
     */
    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    private ?string $firstName = null;

    /**
     * Nom de famille de l'utilisateur
     */
    #[ORM\Column(type: 'string', length: 50)]
    private ?string $lastName = null;

    /**
     * Nom d'utilisateur personnalisé
     * Peut être différent de l'email
     */
    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: 'Le nom d\'utilisateur est obligatoire.')]
    private ?string $username = null;

    // ========================================
    // TIMESTAMPS
    // ========================================
    
    /**
     * Date de création du compte
     * Immutable pour garantir l'intégrité
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Date de dernière modification du profil
     * Mise à jour automatiquement
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    // ========================================
    // COORDONNÉES ET ADRESSES
    // ========================================
    
    /**
     * Nom complet de l'utilisateur
     * Utilisé pour l'affichage et les commandes
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $nom = null;

    /**
     * Adresse postale principale
     * Pour la facturation et la correspondance
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adressePostale = null;

    /**
     * Numéro de téléphone
     * Pour les contacts et la livraison
     */
    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $telephone = null;

    /**
     * Adresse de livraison
     * Peut être différente de l'adresse postale
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $adresseLivraison = null;

    // ========================================
    // GETTERS ET SETTERS
    // ========================================

    /**
     * Récupère l'identifiant unique
     */
    public function getId(): ?int 
    { 
        return $this->id; 
    }

    /**
     * Récupère l'email de connexion
     */
    public function getEmail(): ?string 
    { 
        return $this->email; 
    }

    /**
     * Définit l'email de connexion
     */
    public function setEmail(string $email): self 
    { 
        $this->email = $email; 
        return $this; 
    }

    /**
     * Récupère l'identifiant principal pour l'authentification
     * Utilise l'email comme identifiant unique
     */
    public function getUserIdentifier(): string 
    { 
        return $this->email ?? ''; 
    }

    /**
     * Récupère le nom d'utilisateur
     */
    public function getUsername(): string 
    { 
        return $this->username ?? ''; 
    }

    /**
     * Définit le nom d'utilisateur
     */
    public function setUsername(string $username): self 
    { 
        $this->username = $username; 
        return $this; 
    }

    /**
     * Récupère les rôles de sécurité
     * Garantit que ROLE_USER est toujours présent
     */
    public function getRoles(): array 
    {
        $roles = $this->roles;
        // Assure que ROLE_USER est toujours présent
        if (!in_array('ROLE_USER', $roles)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    /**
     * Définit les rôles de sécurité
     */
    public function setRoles(array $roles): self 
    { 
        $this->roles = $roles; 
        return $this; 
    }

    /**
     * Récupère le mot de passe hashé
     */
    public function getPassword(): string 
    { 
        return $this->password; 
    }

    /**
     * Définit le mot de passe hashé
     */
    public function setPassword(string $password): self 
    { 
        $this->password = $password; 
        return $this; 
    }

    /**
     * Nettoie les données sensibles
     * Requis par l'interface UserInterface
     */
    public function eraseCredentials(): void 
    {
        // Nettoyage des données sensibles si nécessaire
        // Le mot de passe est déjà hashé, pas besoin de nettoyer
    }

    /**
     * Récupère le prénom
     */
    public function getFirstName(): ?string 
    { 
        return $this->firstName; 
    }

    /**
     * Définit le prénom
     */
    public function setFirstName(string $firstName): self 
    { 
        $this->firstName = $firstName; 
        return $this; 
    }

    /**
     * Récupère le nom de famille
     */
    public function getLastName(): ?string 
    { 
        return $this->lastName; 
    }

    /**
     * Définit le nom de famille
     */
    public function setLastName(string $lastName): self 
    { 
        $this->lastName = $lastName; 
        return $this; 
    }

    /**
     * Récupère le nom complet
     */
    public function getNom(): ?string 
    { 
        return $this->nom; 
    }

    /**
     * Définit le nom complet
     */
    public function setNom(?string $nom): self 
    { 
        $this->nom = $nom; 
        return $this; 
    }

    /**
     * Récupère l'adresse postale
     */
    public function getAdressePostale(): ?string 
    { 
        return $this->adressePostale; 
    }

    /**
     * Définit l'adresse postale
     */
    public function setAdressePostale(?string $adressePostale): self 
    { 
        $this->adressePostale = $adressePostale; 
        return $this; 
    }

    /**
     * Récupère le numéro de téléphone
     */
    public function getTelephone(): ?string 
    { 
        return $this->telephone; 
    }

    /**
     * Définit le numéro de téléphone
     */
    public function setTelephone(?string $telephone): self 
    { 
        $this->telephone = $telephone; 
        return $this; 
    }

    /**
     * Récupère l'adresse de livraison
     */
    public function getAdresseLivraison(): ?string 
    { 
        return $this->adresseLivraison; 
    }

    /**
     * Définit l'adresse de livraison
     */
    public function setAdresseLivraison(?string $adresseLivraison): self 
    { 
        $this->adresseLivraison = $adresseLivraison; 
        return $this; 
    }

    /**
     * Récupère la date de création
     */
    public function getCreatedAt(): ?\DateTimeImmutable 
    { 
        return $this->createdAt; 
    }

    /**
     * Définit la date de création
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): self 
    { 
        $this->createdAt = $createdAt; 
        return $this; 
    }

    /**
     * Récupère la date de dernière modification
     */
    public function getUpdatedAt(): ?\DateTimeImmutable 
    { 
        return $this->updatedAt; 
    }

    /**
     * Définit la date de dernière modification
     */
    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self 
    { 
        $this->updatedAt = $updatedAt; 
        return $this; 
    }
}
