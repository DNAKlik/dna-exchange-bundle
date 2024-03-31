<?php

namespace DnaKlik\DnaExchangeBundle\Entity;

use DnaKlik\DnaExchangeBundle\Repository\DnaExchangeUserStampRepository;
use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DnaExchangeUserStampRepository::class)]
class DnaExchangeUserStamp
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?UserProfile $profile = null;

    #[ORM\Column(length: 4)]
    private ?string $Stamp = null;

    #[ORM\Column]
    private ?int $Counter = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getProfile(): ?UserProfile
    {
        return $this->profile;
    }

    public function setProfile(?UserProfile $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    public function getStamp(): ?string
    {
        return $this->Stamp;
    }

    public function setStamp(string $Stamp): static
    {
        $this->Stamp = $Stamp;

        return $this;
    }

    public function getCounter(): ?int
    {
        return $this->Counter;
    }

    public function setCounter(int $Counter): static
    {
        $this->Counter = $Counter;

        return $this;
    }
}
