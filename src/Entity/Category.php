<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="categories")
 */
#[ApiResource(
    attributes: ["security" => "is_granted('IS_AUTHENTICATED_FULLY')"],
    normalizationContext: ['groups' => ['read:category:collection']],
    collectionOperations: [
        'get' => [
            "security" => "is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')",
            "security_message" => "Only User can add show Category.",
        ],
        'post' => [
            'denormalization_context' => ['groups' => ['write:category:collection']],
            "security" => "is_granted('ROLE_ADMIN')",
            "security_message" => "Only admins can add a Category.",
        ]
    ],
    itemOperations: [
        'get' => [
            'normalization_context' => [
                'groups' => ['read:category:collection', 'read:category:item']
            ],
            "security" => "is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')",
            "security_message" => "Only User can show Category."
        ],
        'put' => [
            'denormalization_context' => ['groups' => ['write:category:collection']],
            "security" => "is_granted('ROLE_ADMIN') ",
            "security_message" => "Only admins can edit a Category.",
        ],
        'delete' => [
            "security" => "is_granted('ROLE_ADMIN') ",
            "security_message" => "Only admins can delete Category.",
        ]
    ]
)]
class Category
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:category:collection', 'read:user:item'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:category:collection', 'read:user:item', 'write:category:collection']),
        Assert\NotBlank(),
        Assert\Length(min: 5, max: 100)
    ]
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    #[
        Groups(['read:category:item', 'write:category:collection']),
        Assert\Length(max: 255)
    ]
    private $description;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:category:item'])]
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:category:item'])]
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Job::class, mappedBy="category")
     */
    #[Groups(['read:category:collection'])]
    private $jobs;

    public function __construct()
    {
        $this->jobs = new ArrayCollection();
    }

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

    /**
     * @return Collection|Job[]
     */
    public function getJobs(): Collection
    {
        return $this->jobs;
    }

    public function addJob(Job $job): self
    {
        if (!$this->jobs->contains($job)) {
            $this->jobs[] = $job;
            $job->setCategory($this);
        }

        return $this;
    }

    public function removeJob(Job $job): self
    {
        if ($this->jobs->removeElement($job)) {
            // set the owning side to null (unless already changed)
            if ($job->getCategory() === $this) {
                $job->setCategory(null);
            }
        }

        return $this;
    }
}
