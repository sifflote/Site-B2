<?php

namespace App\Entity\B2;

use App\Repository\B2\UhRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UhRepository::class)]
#[ORM\Table(name: 'b2_uh')]
class Uh
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $numero;

    #[ORM\Column(type: 'string', length: 255)]
    private $designation;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $antenne;

    #[ORM\OneToMany(mappedBy: 'uh', targetEntity: Titre::class)]
    private $titres;

    public function __construct()
    {
        $this->titres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;

        return $this;
    }

    public function getDesignation(): ?string
    {
        return $this->designation;
    }

    public function setDesignation(string $designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    public function getAntenne(): ?string
    {
        return $this->antenne;
    }

    public function setAntenne(?string $antenne): self
    {
        $this->antenne = $antenne;

        return $this;
    }

    /**
     * @return Collection<int, Titre>
     */
    public function getTitres(): Collection
    {
        return $this->titres;
    }

    public function addTitre(Titre $titre): self
    {
        if (!$this->titres->contains($titre)) {
            $this->titres[] = $titre;
            $titre->setUh($this);
        }

        return $this;
    }

    public function removeTitre(Titre $titre): self
    {
        if ($this->titres->removeElement($titre)) {
            // set the owning side to null (unless already changed)
            if ($titre->getUh() === $this) {
                $titre->setUh(null);
            }
        }

        return $this;
    }
}
