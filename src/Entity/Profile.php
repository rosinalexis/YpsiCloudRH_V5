<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProfileRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProfileRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="profiles")
 */
#[ApiResource(
    collectionOperations: [
        'post' => [
            'denormalization_context' => [
                'groups' => ['write:profile:collection'],
                'normalization_context' => ['groups' => ['read:profile:collection', 'read:profile:item']]
            ],
        ]
    ],
    itemOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['read:profile:collection', 'read:profile:item']]
        ],
        'put' => [
            'denormalization_context' => ['groups' => ['write:profile:put']],
            "security" => "is_granted('ROLE_ADMIN') or object.getId() == user.getProfile().getId()",
            "security_message" => "You must be an admins or user owner for edit this profile."
        ],
        'delete' => [
            "security" => "is_granted('ROLE_ADMIN') ",
            "security_message" => "Only admins can delete users profiles.",
        ],
    ]
)]
class Profile
{
    const GENDER_MALE = "monsieur";
    const GENDER_FEMALE = "madame";
    const GENDER_GIRL = "mademoiselle";
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[
        Groups([
            'read:profile:collection',
            'read:user:item'
        ]),
    ]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups([
            'read:profile:collection',
            'read:user:collection',
            'write:profile:collection',
            'read:user:item'
        ]),
        Assert\Length(min: 5, max: 255),
        Assert\NotBlank()
    ]
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups([
            'read:profile:collection', 'write:profile:collection',
            'read:user:collection'
        ]),
        Assert\Length(min: 5, max: 255),
        Assert\NotBlank(),
    ]
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups([
            'read:profile:item',
            'read:user:collection',
            'write:profile:collection'
        ]),
        Assert\Choice([self::GENDER_MALE, self::GENDER_FEMALE, self::GENDER_GIRL]),
        Assert\NotBlank()
    ]
    private $gender;

    /**
     * @ORM\Column(type="string", length=30)
     */
    #[
        Groups([
            'read:profile:item',
            'write:profile:collection',
            'write:profile:put',
        ]),
        Assert\Length(min: 10, max: 20),
        Assert\NotBlank()

    ]
    // Assert\Regex(pattern: '^[0-9\-\(\)\/\+\s]*$')
    private $phone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups([
            'read:profile:item',
            'write:profile:collection',
            'write:profile:put'
        ]),
        Assert\Length(min: 8, max: 255),
        Assert\NotBlank()
    ]
    private $address;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[
        Groups([
            'read:profile:item',
            'write:profile:collection'
        ]),
        Assert\NotBlank(),
        Assert\Type("DateTimeImmutable")
    ]
    private $birthdate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    #[
        Groups([
            'read:profile:item',
            'write:profile:collection',
            'write:profile:put'
        ]),
        Assert\Length(max: 255, maxMessage: "Max description value."),
    ]
    private $description;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:profile:collection'])]
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:profile:item'])]
    private $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity=User::class, mappedBy="profile")
     */
    #[Groups(['read:profile:item'])]
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = strtolower($lastname);

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = strtolower($firstname);

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = strtolower($gender);

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = strtolower($address);

        return $this;
    }

    public function getBirthdate(): ?\DateTimeImmutable
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTimeImmutable $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = strtolower($description);

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setProfile(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getProfile() !== $this) {
            $user->setProfile($this);
        }

        $this->user = $user;

        return $this;
    }
}
