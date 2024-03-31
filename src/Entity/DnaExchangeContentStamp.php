<?php

namespace DnaKlik\DnaExchangeBundle\Entity;

use App\Repository\DnaExchangeContentStampRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DnaExchangeContentStampRepository::class)]
class DnaExchangeContentStamp
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?DnaExchangeContent $dnaExchangeContent = null;

    #[ORM\Column(length: 4)]
    private ?string $Stamp = null;

    #[ORM\Column]
    private ?int $Counter = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDnaExchangeContent(): ?DnaExchangeContent
    {
        return $this->dnaExchangeContent;
    }

    public function setDnaExchangeContent(?DnaExchangeContent $dnaExchangeContent): static
    {
        $this->dnaExchangeContent = $dnaExchangeContent;

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
