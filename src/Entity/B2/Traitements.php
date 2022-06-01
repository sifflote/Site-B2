<?php

namespace App\Entity\B2;

use App\Entity\Users;
use App\Repository\B2\TraitementsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TraitementsRepository::class)]
#[ORM\Table(name: 'b2_traitements')]
class Traitements
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: 'b2_traitements')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\ManyToOne(targetEntity: Titre::class, inversedBy: 'traitements')]
    #[ORM\JoinColumn(nullable: false)]
    private $titre;

    #[ORM\ManyToOne(targetEntity: Observations::class, inversedBy: 'traitements')]
    #[ORM\JoinColumn(nullable: false)]
    private $observation;

    #[ORM\Column(type: 'text', nullable: true)]
    private $precisions;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $traite_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTitre(): ?Titre
    {
        return $this->titre;
    }

    public function setTitre(?Titre $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getObservation(): ?Observations
    {
        return $this->observation;
    }

    public function setObservation(?Observations $observation): self
    {
        $this->observation = $observation;

        return $this;
    }

    public function getPrecisions(): ?string
    {
        return $this->precisions;
    }

    public function setPrecisions(?string $precisions): self
    {
        $this->precisions = $precisions;

        return $this;
    }

    public function getTraiteAt(): ?\DateTimeImmutable
    {
        return $this->traite_at;
    }

    public function setTraiteAt(?\DateTimeImmutable $traite_at): self
    {
        $this->traite_at = $traite_at;

        return $this;
    }
}
