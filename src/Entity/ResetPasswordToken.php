<?php

namespace App\Entity;

use App\Repository\ResetPasswordTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User; // 🔗 On importe l'entité User pour la relation

#[ORM\Entity(repositoryClass: ResetPasswordTokenRepository::class)]
class ResetPasswordToken
{
    // 🔑 Clé primaire auto-incrémentée
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // 🧬 Token unique envoyé à l'utilisateur
    #[ORM\Column(length: 255)]
    private ?string $token = null;

    // ⏰ Date d'expiration du token (ex: dans 2h)
    #[ORM\Column]
    private ?\DateTimeImmutable $expiresAt = null;

    // 🚩 Indique si le lien a déjà été utilisé
    #[ORM\Column]
    private ?bool $isUsed = null;

    // 👤 Relation avec un utilisateur — le propriétaire du token
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    // 🧱 Accesseurs (getters/setters)

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function isUsed(): ?bool
    {
        return $this->isUsed;
    }

    public function setIsUsed(bool $isUsed): static
    {
        $this->isUsed = $isUsed;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }
}
