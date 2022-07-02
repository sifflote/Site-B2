<?php

namespace App\Entity\B2;

use App\Entity\Users;
use App\Repository\B2\HistoriqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoriqueRepository::class)]
#[ORM\Table(name: 'b2_historiques')]
class Historique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: 'b2Historiques')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    #[ORM\Column(type: 'datetime_immutable')]
    private $dateAt;

    #[ORM\Column(type: 'text')]
    private $context;

    #[ORM\ManyToOne(targetEntity: Observations::class, inversedBy: 'historiques')]
    private $observation;

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

    public function getDateAt(): ?\DateTimeImmutable
    {
        return $this->dateAt;
    }

    public function setDateAt(\DateTimeImmutable $dateAt): self
    {
        $this->dateAt = $dateAt;

        return $this;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setContext(string $context): self
    {
        $this->context = $context;

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
}
