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

    #[ORM\Column(type: 'integer')]
    private $verify2;

    #[ORM\Column(type: 'integer')]
    private $newLine = 0;

    #[ORM\Column(type: 'integer')]
    private $countLine = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $rapproche;

    #[ORM\Column(type: 'boolean')]
    private $isPurge = 0;

    #[ORM\Column(type: 'integer')]
    private $count_obs = 0;

    #[ORM\Column(type: 'boolean')]
    private $withObs;

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

    public function getVerify2(): ?int
    {
        return $this->verify2;
    }

    public function setVerify2(int $verify2): self
    {
        $this->verify2 = $verify2;

        return $this;
    }

    public function getNewLine(): ?int
    {
        return $this->newLine;
    }

    public function setNewLine(int $newLine): self
    {
        $this->newLine = $newLine;

        return $this;
    }

    public function getCountLine(): ?int
    {
        return $this->countLine;
    }

    public function setCountLine(int $countLine): self
    {
        $this->countLine = $countLine;

        return $this;
    }

    public function getRapproche(): ?int
    {
        return $this->rapproche;
    }

    public function setRapproche(?int $rapproche): self
    {
        $this->rapproche = $rapproche;

        return $this;
    }

    public function getIsPurge(): ?bool
    {
        return $this->isPurge;
    }

    public function setIsPurge(bool $isPurge): self
    {
        $this->isPurge = $isPurge;

        return $this;
    }

    public function getCountObs(): ?int
    {
        return $this->count_obs;
    }

    public function setCountObs(int $count_obs): self
    {
        $this->count_obs = $count_obs;

        return $this;
    }

    public function getWithObs(): ?bool
    {
        return $this->withObs;
    }

    public function setWithObs(bool $withObs): self
    {
        $this->withObs = $withObs;

        return $this;
    }
}
