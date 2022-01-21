<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\EstablishmentRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=EstablishmentRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="establishments")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read:establishment:collection']],
    collectionOperations: [
        'get' => [
            "security" => "is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')",
            "security_message" => "Only User can  show Establishment.",
        ],
        'post' => [
            "denormalization_context" => ['groups' => ['write:establishment:collection']],
            "security" => "is_granted('ROLE_ADMIN')",
            "security_message" => "Only admins can add a Establishment.",
        ]
    ],
    itemOperations: [
        'put' => [
            "denormalization_context" => ['groups' => ['put:establishment:item']],
            "security" => "is_granted('ROLE_ADMIN')",
            "security_message" => "Only admins can edit a establishment.",
        ],
        'get' => [
            'normalization_context' => [
                'groups' => ['read:establishment:collection', 'read:establishment:item']
            ],
            "security" => "is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')",
            "security_message" => "Only User can show an  establishment."
        ],
        'delete' => [
            "security" => "is_granted('ROLE_ADMIN') ",
            "security_message" => "Only admins can delete an establishment.",
        ]
    ]
)]
class Establishment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:establishment:collection',  'read:user:collection'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:establishment:item', 'write:establishment:collection']),
        Assert\NotBlank(),
        Assert\Length(min: 2)
    ]
    private $siret;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:establishment:collection',  'read:user:collection', 'write:establishment:collection']),
        Assert\NotBlank(),
        Assert\Length(min: 2)
    ]
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:establishment:item', 'write:establishment:collection']),
        Assert\NotBlank(),
        Assert\Length(min: 10)
    ]
    private $phone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:establishment:collection', 'write:establishment:collection']),
        Assert\NotBlank(),
        Assert\Length(min: 2)
    ]
    private $departmentName;

    /**
     * @ORM\Column(type="integer")
     */
    #[
        Groups(['read:establishment:collection', 'write:establishment:collection']),
        Assert\NotBlank(),
    ]
    private $departmentNumber;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:establishment:collection', 'write:establishment:collection']),
        Assert\NotBlank(),
        Assert\Length(min: 2)
    ]
    private $region;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="establishment")
     */
    #[Groups(['read:establishment:item'])]
    private $users;

    /**
     * @ORM\Column(type="json")
     */
    #[Groups(['read:establishment:collection', 'put:establishment:item'])]
    private $setting = [];

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:establishment:item'])]
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:establishment:item'])]
    private $updatedAt;


    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): self
    {
        $this->siret = $siret;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = strtolower($name);

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = strtolower($phone);

        return $this;
    }

    public function getDepartmentName(): ?string
    {
        return $this->departmentName;
    }

    public function setDepartmentName(string $departmentName): self
    {
        $this->departmentName = strtolower($departmentName);

        return $this;
    }

    public function getDepartmentNumber(): ?int
    {
        return $this->departmentNumber;
    }

    public function setDepartmentNumber(int $departmentNumber): self
    {
        $this->departmentNumber = $departmentNumber;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): self
    {
        $this->region = strtolower($region);

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addEstablishment($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeEstablishment($this);
        }

        return $this;
    }

    public function getSetting(): ?array
    {
        return $this->setting;
    }

    public function setSetting(array $setting): self
    {
        $this->setting = $setting;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateTimestamps()
    {
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTimeImmutable);
        }

        $this->setUpdatedAt(new \DateTimeImmutable);
    }
}
