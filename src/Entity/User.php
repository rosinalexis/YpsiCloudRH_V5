<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="users")
 */
#[ApiResource(
    normalizationContext: ['groups' => ['read:user:collection']],
    //denormalizationContext: ['groups' => ['write:user:collection']],
    collectionOperations: [
        'get',
        'post' => [
            'denormalization_context' => ['groups' => ['write:user:collection']],
            "validation_groups" => ['Default', 'write:user:collection'],
            "security" => "is_granted('ROLE_ADMIN')",
            "security_message" => "Only admins can add user.",
        ]
    ],
    itemOperations: [
        'put' => [
            'denormalization_context' => ['groups' => ['write:user:put']],
            "security" => "is_granted('ROLE_ADMIN') ",
            "security_message" => "Only admins can edit user.",
        ],
        'delete' => [
            "security" => "is_granted('ROLE_ADMIN') ",
            "security_message" => "Only admins can edit user.",
        ],
        'get' => [
            'normalization_context' => ['groups' => ['read:user:collection', 'read:user:item']]
        ],

    ]

)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:user:collection'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * 
     */
    #[
        Groups([
            'read:user:collection',
            'write:user:collection'
        ]),
        Assert\Email,
        Assert\NotBlank
    ]
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    #[
        Groups([
            'read:user:item',
            'write:user:collection'
        ]),
        Assert\NotBlank,
        Assert\Choice([["ROLE_USER"], ["ROLE_ADMIN"]])
    ]
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    #[
        Groups(['write:user:collection']),
        SerializedName("password"),
        Assert\NotBlank(groups: ['write:user:collection']),
        Assert\Length(min: 5),
    ]
    private $plainPassword;

    /**
     * @ORM\Column(type="boolean")
     */
    #[
        Groups([
            'read:user:item',
            'write:user:collection',
            'write:user:put',
            'read:user:collection'
        ]),
        Assert\Type('bool')
    ]
    private $isActivated;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:user:item'])]
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:user:item'])]
    private $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity=Profile::class, cascade={"persist", "remove"})
     */
    #[Groups(['read:user:item'])]
    private $profile;

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
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // // guarantee every user at least has ROLE_USER
        // $roles[] = 'ROLE_USER';

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
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getIsActivated(): ?bool
    {
        return $this->isActivated;
    }

    public function setIsActivated(bool $isActivated): self
    {
        $this->isActivated = $isActivated;

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

    /**
     * Get the value of plainPassword
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set the value of plainPassword
     *
     * @return  self
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }
}
