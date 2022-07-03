<?php

namespace App\Entity\B2;

use App\Repository\B2\DebiteursRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DebiteursRepository::class)]
#[ORM\Table(name: 'b2_debiteurs')]
class Debiteurs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 2)]
    private $classe;

    #[ORM\Column(type: 'integer')]
    private $payeur;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getClasse(): ?string
    {
        return $this->classe;
    }

    public function setClasse(string $classe): self
    {
        $this->classe = $classe;

        return $this;
    }

    public function getPayeur(): ?int
    {
        return $this->payeur;
    }

    public function setPayeur(int $payeur): self
    {
        $this->payeur = $payeur;

        return $this;
    }
}
