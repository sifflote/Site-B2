<?php

namespace App\Entity\B2;

use App\Repository\B2\ExtractionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExtractionsRepository::class)]
#[ORM\Table(name: 'b2_extractions')]
class Extractions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $name;

    #[ORM\Column(type: 'datetime_immutable')]
    private $import_at;

    #[ORM\Column(type: 'integer')]
    private $files;

    #[ORM\Column(type: 'integer')]
    private $verify;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getImportAt(): ?\DateTimeImmutable
    {
        return $this->import_at;
    }

    public function setImportAt(\DateTimeImmutable $import_at): self
    {
        $this->import_at = $import_at;

        return $this;
    }

    public function getFiles(): ?int
    {
        return $this->files;
    }

    public function setFiles(int $files): self
    {
        $this->files = $files;

        return $this;
    }

    public function getVerify(): ?int
    {
        return $this->verify;
    }

    public function setVerify(int $verify): self
    {
        $this->verify = $verify;

        return $this;
    }
}
