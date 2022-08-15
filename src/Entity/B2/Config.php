<?php

namespace App\Entity\B2;

use App\Repository\B2\ConfigRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigRepository::class)]
#[ORM\Table(name: 'b2_config')]
class Config
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 2)]
    private $field;

    #[ORM\Column(type: 'datetime_immutable')]
    private $valid_at;

    #[ORM\Column(type: 'datetime_immutable')]
    private $begin_at;

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

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getValidAt(): ?\DateTimeImmutable
    {
        return $this->valid_at;
    }

    public function setValidAt(\DateTimeImmutable $valid_at): self
    {
        $this->valid_at = $valid_at;

        return $this;
    }

    public function getBeginAt(): ?\DateTimeImmutable
    {
        return $this->begin_at;
    }

    public function setBeginAt(\DateTimeImmutable $begin_at): self
    {
        $this->begin_at = $begin_at;

        return $this;
    }
}
