<?php

namespace App\Entity\B2;

use App\Repository\B2\PostitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostitRepository::class)]
#[ORM\Table(name: 'b2_postit')]
class Postit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $postit;

    #[ORM\Column(type: 'date')]
    private $postit_at;

    #[ORM\Column(type: 'bigint')]
    private $ipp;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostit(): ?string
    {
        return $this->postit;
    }

    public function setPostit(string $postit): self
    {
        $this->postit = $postit;

        return $this;
    }

    public function getPostitAt(): ?\DateTimeInterface
    {
        return $this->postit_at;
    }

    public function setPostitAt(\DateTimeInterface $postit_at): self
    {
        $this->postit_at = $postit_at;

        return $this;
    }

    public function getIpp(): ?int
    {
        return $this->ipp;
    }

    public function setIpp(int $ipp): self
    {
        $this->ipp = $ipp;

        return $this;
    }

}
