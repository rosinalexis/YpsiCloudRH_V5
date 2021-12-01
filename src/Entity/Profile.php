<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProfileRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\ProfileImageUploadAction;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProfileRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="profiles")
 *  @Vich\Uploadable()
 */
#[ApiResource(
    collectionOperations: [
        'post' => [
            'denormalization_context' => [
                'groups' => ['write:profile:collection'],
                'normalization_context' => ['groups' => ['read:profile:collection', 'read:profile:item']],
                "validation_groups" => ["write:profile:collection"],
            ],
        ],
        'post-profile-image' => [
            "method" => "POST",
            "path" => "/profiles/{id}/add/image",
            "controller" => ProfileImageUploadAction::class,
            "deserialize" => false,
            "denormalization_context" => [
                "groups" => ["post:profile:image"]
            ],
            "security" => "is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')",
            "security_message" => "You must be an admins or user owner for edit this profile.",
            "validation_groups" => ["post:profile:image"]
        ]
    ],
    itemOperations: [
        'get' => [
            'normalization_context' => ['groups' => ['read:profile:collection', 'read:profile:item']]
        ],
        'put' => [
            'denormalization_context' => ['groups' => ['write:profile:put']],
            "security" => "is_granted('ROLE_ADMIN') or object.getId() == user.getProfile().getId()",
            "security_message" => "You must be an admins or user owner for edit this profile.",
            "validation_groups" => ["write:profile:put"]
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
            'read:user:item',
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
            'read:user:item',
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
            'read:user:item',
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
            'read:user:item',
            'write:profile:collection',
            'write:profile:put'
        ]),
        Assert\Length(max: 255, maxMessage: "Max description value."),
    ]
    private $description;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:profile:collection', 'read:user:item'])]
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:profile:item', 'read:user:item'])]
    private $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity=User::class, mappedBy="profile")
     */
    #[Groups(['read:profile:item'])]
    private $user;

    /**
     * @Vich\UploadableField(mapping="profiles", fileNameProperty="url")
     * @var File|null
     */
    //#[Assert\NotNull(groups: ["post:profile:image"])]
    #[Groups(['post:profile:image', 'read:profile:collection'])]
    private $file;


    /**
     * @ORM\Column(type="string", length=255, nullable = true)
     */
    #[Groups(['read:profile:item'])]
    private $url;

    /**
     * @ORM\Column(type="string", length=255, nullable = true)
     */
    #[Groups(['read:profile:item', 'read:user:item'])]
    private $fileUrl;




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
        $this->phone = strtolower($phone);

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

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file = null)
    {
        $this->file = $file;
        if (null !== $file) {
            $this->setUpdatedAt(new \DateTimeImmutable());
        }

        return $this;
    }


    public function getUrl()
    {
        return $this->url;
    }


    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }


    public function getFileUrl()
    {
        return $this->fileUrl;
    }

    /**
     * @ORM\PostUpdate
     */
    public function setFileUrl()
    {
        if (null !== $this->getUrl()) {
            $this->fileUrl = '/images/profiles/' . $this->getUrl();
        } else {
            $this->fileUrl = null;
        }

        return $this;
    }
}
