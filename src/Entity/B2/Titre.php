<?php

namespace App\Entity\B2;

use App\Repository\B2\TitreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TitreRepository::class)]
#[ORM\Table(name: 'b2_titre')]
class Titre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'bigint')]
    private $reference;

    #[ORM\Column(type: 'string', length: 2)]
    private $type;

    #[ORM\Column(type: 'string', length: 2)]
    private $classe;

    #[ORM\Column(type: 'integer')]
    private $iep;

    #[ORM\Column(type: 'bigint')]
    private $ipp;

    #[ORM\Column(type: 'integer')]
    private $facture;

    #[ORM\Column(type: 'string', length: 50)]
    private $name;

    #[ORM\Column(type: 'date')]
    private $enter_at;

    #[ORM\Column(type: 'date')]
    private $exit_at;

    #[ORM\Column(type: 'float')]
    private $montant;

    #[ORM\Column(type: 'float', nullable: true)]
    private $encaissement;

    #[ORM\Column(type: 'float', nullable: true)]
    private $restantdu;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $pec;

    #[ORM\Column(type: 'integer')]
    private $lot;

    #[ORM\Column(type: 'integer')]
    private $payeur;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $code_rejet;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $desc_rejet;

    #[ORM\Column(type: 'date')]
    private $cree_at;

    #[ORM\Column(type: 'date')]
    private $rejet_at;

    #[ORM\Column(type: 'string', length: 50)]
    private $designation;

    #[ORM\ManyToOne(targetEntity: Uh::class, inversedBy: 'titres')]
    private $uh;

    #[ORM\Column(type: 'bigint')]
    private $insee;

    #[ORM\Column(type: 'integer')]
    private $rang;

    #[ORM\Column(type: 'date')]
    private $naissance_at;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $contrat;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private $naissance_hf;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $rprs;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $maj_at;

    #[ORM\OneToMany(mappedBy: 'titre', targetEntity: Traitements::class, orphanRemoval: true)]
    private $traitements;

    #[ORM\Column(type: 'datetime_immutable')]
    private $extraction_at;

    #[ORM\Column(type: 'boolean')]
    private $is_rapproche = false;

    public function __construct()
    {
        $this->traitements = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?int
    {
        return $this->reference;
    }

    public function setReference(int $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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

    public function getIep(): ?int
    {
        return $this->iep;
    }

    public function setIep(int $iep): self
    {
        $this->iep = $iep;

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

    public function getFacture(): ?int
    {
        return $this->facture;
    }

    public function setFacture(int $facture): self
    {
        $this->facture = $facture;

        return $this;
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

    public function getEnterAt(): ?\DateTimeInterface
    {
        return $this->enter_at;
    }

    public function setEnterAt(\DateTimeInterface $enter_at): self
    {
        $this->enter_at = $enter_at;

        return $this;
    }

    public function getExitAt(): ?\DateTimeInterface
    {
        return $this->exit_at;
    }

    public function setExitAt(\DateTimeInterface $exit_at): self
    {
        $this->exit_at = $exit_at;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getEncaissement(): ?float
    {
        return $this->encaissement;
    }

    public function setEncaissement(?float $encaissement): self
    {
        $this->encaissement = $encaissement;

        return $this;
    }

    public function getRestantdu(): ?float
    {
        return $this->restantdu;
    }

    public function setRestantdu(?float $restantdu): self
    {
        $this->restantdu = $restantdu;

        return $this;
    }

    public function getPec(): ?string
    {
        return $this->pec;
    }

    public function setPec(?string $pec): self
    {
        $this->pec = $pec;

        return $this;
    }

    public function getLot(): ?int
    {
        return $this->lot;
    }

    public function setLot(int $lot): self
    {
        $this->lot = $lot;

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

    public function getCodeRejet(): ?string
    {
        return $this->code_rejet;
    }

    public function setCodeRejet(?string $code_rejet): self
    {
        $this->code_rejet = $code_rejet;

        return $this;
    }

    public function getDescRejet(): ?string
    {
        return $this->desc_rejet;
    }

    public function setDescRejet(?string $desc_rejet): self
    {
        $this->desc_rejet = $desc_rejet;

        return $this;
    }

    public function getCreeAt(): ?\DateTimeInterface
    {
        return $this->cree_at;
    }

    public function setCreeAt(\DateTimeInterface $cree_at): self
    {
        $this->cree_at = $cree_at;

        return $this;
    }

    public function getRejetAt(): ?\DateTimeInterface
    {
        return $this->rejet_at;
    }

    public function setRejetAt(\DateTimeInterface $rejet_at): self
    {
        $this->rejet_at = $rejet_at;

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

    public function getRelation(): ?string
    {
        return $this->relation;
    }

    public function setRelation(string $relation): self
    {
        $this->relation = $relation;

        return $this;
    }

    public function getUh(): ?Uh
    {
        return $this->uh;
    }

    public function setUh(?Uh $uh): self
    {
        $this->uh = $uh;

        return $this;
    }

    public function getInsee(): ?int
    {
        return $this->insee;
    }

    public function setInsee(int $insee): self
    {
        $this->insee = $insee;

        return $this;
    }

    public function getRang(): ?int
    {
        return $this->rang;
    }

    public function setRang(int $rang): self
    {
        $this->rang = $rang;

        return $this;
    }

    public function getNaissanceAt(): ?\DateTimeInterface
    {
        return $this->naissance_at;
    }

    public function setNaissanceAt(\DateTimeInterface $naissance_at): self
    {
        $this->naissance_at = $naissance_at;

        return $this;
    }

    public function getContrat(): ?string
    {
        return $this->contrat;
    }

    public function setContrat(?string $contrat): self
    {
        $this->contrat = $contrat;

        return $this;
    }

    public function getNaissanceHf(): ?string
    {
        return $this->naissance_hf;
    }

    public function setNaissanceHf(?string $naissance_hf): self
    {
        $this->naissance_hf = $naissance_hf;

        return $this;
    }

    public function getRprs(): ?bool
    {
        return $this->rprs;
    }

    public function setRprs(?bool $rprs): self
    {
        $this->rprs = $rprs;

        return $this;
    }

    public function getMajAt(): ?\DateTimeImmutable
    {
        return $this->maj_at;
    }

    public function setMajAt(\DateTimeImmutable $maj_at): self
    {
        $this->maj_at = $maj_at;

        return $this;
    }

    /**
     * @return Collection<int, Traitements>
     */
    public function getTraitements(): Collection
    {
        return $this->traitements;
    }

    public function addTraitement(Traitements $traitement): self
    {
        if (!$this->traitements->contains($traitement)) {
            $this->traitements[] = $traitement;
            $traitement->setTitre($this);
        }

        return $this;
    }

    public function removeTraitement(Traitements $traitement): self
    {
        if ($this->traitements->removeElement($traitement)) {
            // set the owning side to null (unless already changed)
            if ($traitement->getTitre() === $this) {
                $traitement->setTitre(null);
            }
        }

        return $this;
    }

    public function getExtractionAt(): ?\DateTimeImmutable
    {
        return $this->extraction_at;
    }

    public function setExtractionAt(\DateTimeImmutable $extraction_at): self
    {
        $this->extraction_at = $extraction_at;

        return $this;
    }

    public function getIsRapproche(): bool
    {
        return $this->isRapproche;
    }

    public function setIsRapproche(bool $isRapproche): self
    {
        $this->isRapproche = $isRapproche;

        return $this;
    }

}
