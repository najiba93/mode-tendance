<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\Type\FileType;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $prix = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $couleurs = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $tailles = null;

    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    private ?Categorie $categorie = null;

    #[ORM\OneToMany(targetEntity: ImageProduit::class, mappedBy: 'produit', orphanRemoval: true, cascade: ['persist'])]
    private Collection $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getCouleurs(): ?array
    {
        return $this->couleurs;
    }

    public function setCouleurs(?array $couleurs): self
    {
        $this->couleurs = $couleurs;
        return $this;
    }

    public function getTailles(): ?array
    {
        return $this->tailles;
    }

    public function setTailles(?array $tailles): self
    {
        $this->tailles = $tailles;
        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): self
    {
        $this->categorie = $categorie;
        return $this;
    }

    /**
     * @return Collection<int, ImageProduit>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(ImageProduit $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProduit($this);
        }

        return $this;
    }

    public function removeImage(ImageProduit $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProduit() === $this) {
                $image->setProduit(null);
            }
        }

        return $this;
    }
}

