<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\JobAdvertRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=JobAdvertRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="jobs_adverts")
 */
#[ApiResource(
    collectionOperations: [
        'get',
        'post' => [
            'denormalization_context' => ['groups' => ['write:jobAdvert:collection']],
            "security" => "is_granted('ROLE_ADMIN')",
            "security_message" => "Only User can add show jobAdvert.",
        ]
    ],
    itemOperations: [
        'put' => [
            'denormalization_context' => ['groups' => ['write:jobAdvert:put']],
            "security" => "is_granted('ROLE_ADMIN') ",
            "security_message" => "Only admins can edit a jobAdvert.",
        ],
        'get' => [
            'normalization_context' => [
                'groups' => ['read:jobAdvert:collection', 'read:jobAdvert:item']
            ]
        ],
        'delete' => [
            "security" => "is_granted('ROLE_ADMIN') ",
            "security_message" => "Only admins can delete job.",
        ]
    ],
    normalizationContext: ['groups' => ['read:jobAdvert:collection']]
)]
class JobAdvert
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:jobAdvert:collection', 'read:contact:collection'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:jobAdvert:collection', 'read:contact:collection', 'write:jobAdvert:collection', 'write:jobAdvert:put']),
        Assert\NotBlank(),
        Assert\Length(min: 3, max: 100)
    ]
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:jobAdvert:item', 'write:jobAdvert:collection']),
        Assert\NotBlank(),
        Assert\Length(min: 3, max: 100)
    ]
    private $place;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:jobAdvert:item', 'write:jobAdvert:collection']),
        Assert\NotBlank(),
        Assert\Length(min: 3, max: 100)
    ]
    private $compagny;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:jobAdvert:collection', 'write:jobAdvert:collection', 'write:jobAdvert:put']),
        Assert\NotBlank(),
        Assert\Length(min: 3, max: 100)
    ]
    private $contractType;

    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    #[
        Groups(['read:jobAdvert:item', 'write:jobAdvert:collection', 'write:jobAdvert:put']),
        Assert\Length(min: 3, max: 100)
    ]
    private $wage;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    #[
        Groups(['read:jobAdvert:item', 'read:jobAdvert:collection', 'write:jobAdvert:collection', 'write:jobAdvert:put']),
    ]
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    #[
        Groups(['read:jobAdvert:collection', 'write:jobAdvert:collection', 'write:jobAdvert:put']),
        Assert\Type('bool')
    ]
    private $published;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    #[Groups(['read:jobAdvert:item', 'write:jobAdvert:collection', 'write:jobAdvert:put'])]
    private $tasks = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    #[Groups(['read:jobAdvert:item', 'write:jobAdvert:collection', 'write:jobAdvert:put'])]
    private $requirements = [];

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:jobAdvert:collection'])]
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:jobAdvert:item'])]
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=Contact::class, mappedBy="jobReference", orphanRemoval=true)
     */
    #[Groups(['read:jobAdvert:item'])]
    private $contacts;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="jobAdverts")
     * @ORM\JoinColumn(nullable=false)
     */
    #[Groups(['read:jobAdvert:item', 'write:jobAdvert:collection'])]
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=Establishment::class, inversedBy="jobAdverts")
     */
    #[Groups(['read:jobAdvert:collection', 'read:contact:collection', 'write:jobAdvert:collection'])]
    private $establishment;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
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

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(string $place): self
    {
        $this->place = strtolower($place);

        return $this;
    }

    public function getCompagny(): ?string
    {
        return $this->compagny;
    }

    public function setCompagny(string $compagny): self
    {
        $this->compagny = strtolower($compagny);

        return $this;
    }

    public function getContractType(): ?string
    {
        return $this->contractType;
    }

    public function setContractType(string $contractType): self
    {
        $this->contractType = strtolower($contractType);

        return $this;
    }

    public function getWage(): ?string
    {
        return $this->wage;
    }

    public function setWage(string $wage): self
    {
        $this->wage = strtolower($wage);

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

    public function getPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getTasks(): ?array
    {
        return $this->tasks;
    }

    public function setTasks(?array $tasks): self
    {
        $this->tasks = $tasks;

        return $this;
    }

    public function getRequirements(): ?array
    {
        return $this->requirements;
    }

    public function setRequirements(?array $requirements): self
    {
        $this->requirements = $requirements;

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
     * @return Collection|Contact[]
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts[] = $contact;
            $contact->setJobReference($this);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        if ($this->contacts->removeElement($contact)) {
            // set the owning side to null (unless already changed)
            if ($contact->getJobReference() === $this) {
                $contact->setJobReference(null);
            }
        }

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

    public function getEstablishment(): ?Establishment
    {
        return $this->establishment;
    }

    public function setEstablishment(?Establishment $establishment): self
    {
        $this->establishment = $establishment;

        return $this;
    }
}
