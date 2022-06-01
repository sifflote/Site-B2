<?php

namespace App\Entity;

use App\Entity\B2\RejetsParametres;
use App\Entity\B2\Traitements;
use App\Entity\Commerce\Orders;
use App\Entity\Trait\CreatedAtTrait;
use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    use CreatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string', nullable: true)]
    private $password;

    #[ORM\Column(type: 'string', length: 100)]
    private $username;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $google_id;

    #[ORM\Column(type: 'boolean')]
    private $is_verified = false;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private $resetToken;

    #[ORM\OneToMany(mappedBy: 'users', targetEntity: Orders::class)]
    private $orders;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Traitements::class)]
    private $b2_traitements;

    #[ORM\Column(type: 'integer', options: ["default" => 500])]
    private $B2LimitPage = 500;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
        $this->b2_traitements = new ArrayCollection();
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

    public function getGoogleId(): ?Int
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

    public function getB2LimitPage(): ?int
    {
        return $this->B2LimitPage;
    }

    public function setB2LimitPage(int $B2LimitPage): self
    {
        $this->B2LimitPage = $B2LimitPage;

        return $this;
    }


}
