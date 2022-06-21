<?php

namespace App\Entity;

use App\Entity\B2\Historique;
use App\Entity\B2\Parametres;
use App\Entity\B2\RejetsParametres;
use App\Entity\B2\Traitements;
use App\Entity\Commerce\Orders;
use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\EntityListeners(['App\EntityListener\UsersListener'])]
#[UniqueEntity(fields: ['email'], message: 'Cet e-mail est déjà utilisé.')]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\Email()]
    #[Assert\Length(min: 2, max :180)]
    private ?string $email;

    #[ORM\Column(type: 'json')]
    #[Assert\NotNull()]
    private array $roles = [];

    private ?string $plainpassword = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 2, max :50)]
    private ?string $username;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $google_id;

    #[ORM\Column(type: 'boolean')]
    private $is_verified = false;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $resetToken;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\NotNull()]
    private \DateTimeImmutable $createdAt;



    #[ORM\OneToMany(mappedBy: 'users', targetEntity: Orders::class)]
    private $orders;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Traitements::class)]
    private $b2_traitements;

    #[ORM\Column(type: 'integer', options: ['default' => 500])]
    private ?int $b2RejetsPerPage = 500;

    #[ORM\Column(type: 'string', length: 100)]
    private $Fullname;

    #[ORM\Column(type: 'boolean')]
    private $googleUse = 0;

    #[ORM\Column(type: 'boolean')]
    private $mdpUse = 0;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Historique::class)]
    private $b2Historiques;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->b2_traitements = new ArrayCollection();
        $this->b2Historiques = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getGoogleId(): ?string
    {
        return $this->google_id;
    }

    public function setGoogleID(string $google_id): self
    {
        $this->google_id = $google_id;

        return $this;
    }

    public function getIsVerified(): ?Bool
    {
        return $this->is_verified;
    }

    public function setIsVerified(bool $is_verified): self
    {
        $this->is_verified = $is_verified;
        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }


    public function setResetToken($resetToken): self
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    /**
     * @return Collection<int, Orders>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Orders $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setUsers($this);
        }

        return $this;
    }

    public function removeOrder(Orders $order): self
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUsers() === $this) {
                $order->setUsers(null);
            }
        }

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable $createdAt
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return Collection<int, Traitements>
     */
    public function getB2Traitements(): Collection
    {
        return $this->b2_traitements;
    }

    public function addB2Traitement(Traitements $b2Traitement): self
    {
        if (!$this->b2_traitements->contains($b2Traitement)) {
            $this->b2_traitements[] = $b2Traitement;
            $b2Traitement->setUser($this);
        }

        return $this;
    }

    public function removeB2Traitement(Traitements $b2Traitement): self
    {
        if ($this->b2_traitements->removeElement($b2Traitement)) {
            // set the owning side to null (unless already changed)
            if ($b2Traitement->getUser() === $this) {
                $b2Traitement->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlainpassword(): ?string
    {
        return $this->plainpassword;
    }

    /**
     * @param string|null $plainpassword
     */
    public function setPlainpassword(?string $plainpassword): void
    {
        $this->plainpassword = $plainpassword;
    }

    public function getB2RejetsPerPage(): ?int
    {
        return $this->b2RejetsPerPage;
    }

    public function setB2RejetsPerPage(int $b2RejetsPerPage): self
    {
        $this->b2RejetsPerPage = $b2RejetsPerPage;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->Fullname;
    }

    public function setFullname(string $Fullname): self
    {
        $this->Fullname = $Fullname;

        return $this;
    }

    public function getGoogleUse(): ?bool
    {
        return $this->googleUse;
    }

    public function setGoogleUse(?bool $googleUse): self
    {
        $this->googleUse = $googleUse;

        return $this;
    }

    public function getMdpUse(): ?bool
    {
        return $this->mdpUse;
    }

    public function setMdpUse(bool $mdpUse): self
    {
        $this->mdpUse = $mdpUse;

        return $this;
    }

    /**
     * @return Collection<int, Historique>
     */
    public function getB2Historiques(): Collection
    {
        return $this->b2Historiques;
    }

    public function addB2Historique(Historique $b2Historique): self
    {
        if (!$this->b2Historiques->contains($b2Historique)) {
            $this->b2Historiques[] = $b2Historique;
            $b2Historique->setUser($this);
        }

        return $this;
    }

    public function removeB2Historique(Historique $b2Historique): self
    {
        if ($this->b2Historiques->removeElement($b2Historique)) {
            // set the owning side to null (unless already changed)
            if ($b2Historique->getUser() === $this) {
                $b2Historique->setUser(null);
            }
        }

        return $this;
    }

}
