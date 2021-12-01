<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\JobRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=JobRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="jobs")
 */
#[ApiResource(
    attributes: ["security" => "is_granted('IS_AUTHENTICATED_FULLY')"],
    normalizationContext: ['groups' => ['read:job:collection']],
    collectionOperations: [
        'get' => [
            "security" => "is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')",
            "security_message" => "Only User can add show Job.",
        ],
        'post' => [
            'denormalization_context' => ['groups' => ['write:job:collection']],
            "security" => "is_granted('ROLE_ADMIN')",
            "security_message" => "Only admins can add a Job.",
        ]
    ],
    itemOperations: [
        'put' => [
            'denormalization_context' => ['groups' => ['write:job:collection']],
            "security" => "is_granted('ROLE_ADMIN') ",
            "security_message" => "Only admins can edit a job.",
        ],
        'get' => [
            'normalization_context' => [
                'groups' => ['read:job:collection', 'read:job:item']
            ],
            "security" => "is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')",
            "security_message" => "Only User can add show Job."
        ],
        'delete' => [
            "security" => "is_granted('ROLE_ADMIN') ",
            "security_message" => "Only admins can delete job.",
        ]
    ]
)]
class Job
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:job:collection', 'read:user:item'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */

    #[
        Groups(['read:job:collection', 'read:user:collection', 'write:job:collection']),
        Assert\NotBlank(),
        Assert\Length(min: 5)
    ]
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    #[Groups(['read:job:item', 'read:user:item', 'write:job:collection'])]
    private $description;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:job:item', 'read:user:item'])]
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:job:item', 'read:user:item'])]
    private $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity=User::class, mappedBy="job", cascade={"persist", "remove"})
     */
    #[Groups(['read:job:collection'])]
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="jobs")
     */
    #[Groups(['read:job:collection', 'read:user:item'])]
    private $category;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = strtolower($title);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
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
            $this->user->setJob(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getJob() !== $this) {
            $user->setJob($this);
        }

        $this->user = $user;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
