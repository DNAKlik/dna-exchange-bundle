<?php

namespace DnaKlik\DnaExchangeBundle\Entity;

// use DnaKlik\DnaExchangeBundle\Repository\DnaExchangeContentStampRepository;
use DnaKlik\DnaExchangeBundle\Repository\DnaExchangeContentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DnaExchangeContentRepository::class)]
class DnaExchangeContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 4)]
    private ?string $stamp = null;

    #[ORM\OneToMany(mappedBy: 'dnaExchangeContent', targetEntity: DnaExchangeContentStamp::class, orphanRemoval: true, cascade:["persist", "remove"])]
    private Collection $DnaExchangeContentStamp;

    private $property;

    public function __construct()
    {
        $this->DnaExchangeContentStamp = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getStamp(): ?string
    {
        return $this->stamp;
    }

    public function setStamp(string $stamp): static
    {
        $this->stamp = $stamp;

        return $this;
    }

    /**
     * @return Collection<int, DnaExchangeContentStamp>
     */
    public function getDnaExchangeContentStamp(): Collection
    {
        return $this->DnaExchangeContentStamp;
    }

    public function addDnaExchangeContentStamp(DnaExchangeContentStamp $dnaExchangeContentStamp): static
    {
        //if (!$this->DnaExchangeContentStamp->contains($dnaExchangeContentStamp)) {
            $this->DnaExchangeContentStamp->add($dnaExchangeContentStamp);
            $dnaExchangeContentStamp->setDnaExchangeContent($this);
        //}
        return $this;
    }

    public function removeDnaExchangeContentStamp(DnaExchangeContentStamp $dnaExchangeContentStamp): static
    {
        if ($this->DnaExchangeContentStamp->removeElement($dnaExchangeContentStamp)) {
            // set the owning side to null (unless already changed)
            if ($dnaExchangeContentStamp->getDnaExchangeContent() === $this) {
                $dnaExchangeContentStamp->setDnaExchangeContent(null);
            }
        }

        return $this;
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }

    public function setProperty(string $property): static
    {
        $this->property = $property;

        return $this;
    }
}
