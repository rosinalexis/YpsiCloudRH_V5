<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ContactRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Action\ContactDocumentUploadAction;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ContactRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="contacts")
 * @Vich\Uploadable()
 */
#[ApiResource(
    collectionOperations: [
        'get' => [
            "security" => "is_granted('ROLE_ADMIN') ",
            "security_message" => "Only admins can show Contact list.",
        ],
        'post' => [
            'denormalization_context' => ['groups' => ['write:contact:collection']],
        ],
        "post-contact-document" => [
            "method" => "POST",
            "path" => "/contacts/{id}/add/documents",
            "controller" => ContactDocumentUploadAction::class,
            "deserialize" => false,
            "denormalization_context" => [
                "groups" => ["post:contact:document"]
            ],
            "validation_groups" => ["post:contact:document"]
        ]
    ],
    itemOperations: [
        'put' => [
            "denormalization_context" => [
                'groups' => ['write:contact:put']
            ],
            "security" => "is_granted('ROLE_ADMIN')",
            "security_message" => "Only admins can edit a Contact.",
        ],
        'get' => [
            'normalization_context' => [
                'groups' => ['read:contact:collection', 'read:contact:item']
            ],
            "security" => "is_granted('ROLE_ADMIN') ",
            "security_message" => "Only admins can read a Contact.",
        ],
        'delete' => [
            "security" => "is_granted('ROLE_ADMIN') ",
            "security_message" => "Only admins can delete job.",
        ]
    ],
    normalizationContext: ['groups' => ['read:contact:collection']]
)]
class Contact
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    #[Groups(['read:contact:collection', 'read:jobAdvert:item'])]
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:contact:collection', 'read:jobAdvert:item', 'write:contact:collection']),
        Assert\NotBlank(),
        Assert\Length(min: 2, max: 100)
    ]
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:contact:collection', 'read:jobAdvert:item', 'write:contact:collection']),
        Assert\NotBlank(),
        Assert\Length(min: 2, max: 100)
    ]
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[
        Groups(['read:contact:collection', 'write:contact:collection']),
        Assert\NotBlank(),
        Assert\Length(min: 5, max: 150)
    ]
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[
        Groups(['read:contact:item', 'write:contact:collection']),
        Assert\NotBlank(),
        Assert\Length(min: 5, max: 150)
    ]
    private $subject;


    /**
     * @Vich\UploadableField(mapping="contacts", fileNameProperty="cvUrl")
     * @var File|null
     */
    #[Groups(['post:contact:document'])]
    private $cvFile;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['read:contact:collection'])]
    private $cvUrl;


    /**
     * @ORM\Column(type="string", length=255, nullable = true)
     */
    #[Groups(['read:contact:item'])]
    private $cvFileUrl;


    /**
     * @Vich\UploadableField(mapping="contacts", fileNameProperty="coverLetterUrl")
     * @var File|null
     */
    #[Groups(['post:contact:document'])]
    private $coverLetterFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Groups(['read:contact:collection'])]
    private $coverLetterUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable = true)
     */
    #[Groups(['read:contact:item'])]
    private $coverLetterFileUrl;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    #[
        Groups(['read:contact:item', 'write:contact:collection']),
        Assert\Length(max: 255)
    ]
    private $message;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    #[Groups(['read:contact:item', 'write:contact:put'])]
    private $management;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:contact:collection'])]
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    #[Groups(['read:contact:item'])]
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=JobAdvert::class, inversedBy="contacts")
     * @ORM\JoinColumn(nullable=false)
     */
    #[
        Groups(['read:contact:collection', 'write:contact:collection']),
    ]
    private $jobReference;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Groups(['read:contact:collection', 'read:contact:item', 'write:contact:put'])]
    private $state;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = strtolower($lastname);

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = strtolower($email);

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = strtolower($subject);

        return $this;
    }


    public function getCoverLetterUrl(): ?string
    {
        return $this->coverLetterUrl;
    }

    public function setCoverLetterUrl(?string $coverLetterUrl): self
    {
        $this->coverLetterUrl = $coverLetterUrl;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = strtolower($message);

        return $this;
    }

    public function getManagement(): ?array
    {
        return $this->management;
    }

    public function setManagement(?array $management): self
    {
        $this->management = $management;

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


    public function getCvFile(): ?File
    {
        return $this->cvFile;
    }

    public function setCvFile(?File $cvFile = null)
    {
        $this->cvFile = $cvFile;
        if (null !== $cvFile) {
            $this->setUpdatedAt(new \DateTimeImmutable());
        }

        return $this;
    }

    public function getCvUrl(): ?string
    {
        return $this->cvUrl;
    }

    public function setCvUrl(?string $cvUrl): self
    {
        $this->cvUrl = $cvUrl;

        return $this;
    }


    public function getCvFileUrl()
    {
        return $this->cvFileUrl;
    }


    /**
     * @ORM\PostUpdate
     */
    public function setCvFileUrl()
    {
        if (null !== $this->getCvUrl()) {
            $this->cvFileUrl = "/images/contacts/" . $this->getCvUrl();
        } else {
            $this->cvFileUrl = null;
        }

        return $this;
    }


    public function getCoverLetterFile(): ?File
    {
        return $this->coverLetterFile;
    }


    public function setCoverLetterFile(?File $coverLetterFile = null)
    {
        $this->coverLetterFile = $coverLetterFile;
        if (null !== $coverLetterFile) {
            $this->setUpdatedAt(new \DateTimeImmutable());
        }

        return $this;
    }

    public function getCoverLetterFileUrl()
    {
        return $this->coverLetterFileUrl;
    }

    /**
     * @ORM\PostUpdate
     */
    public function setCoverLetterFileUrl()
    {
        if (null !== $this->getCoverLetterUrl()) {
            $this->coverLetterFileUrl = '/images/contacts/' . $this->getCoverLetterUrl();
        } else {
            $this->coverLetterFileUrl = null;
        }

        return $this;
    }

    public function getJobReference(): ?JobAdvert
    {
        return $this->jobReference;
    }

    public function setJobReference(?JobAdvert $jobReference): self
    {
        $this->jobReference = $jobReference;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getFullName(): ?string
    {

        return $this->getLastname() . ' ' . $this->getFirstname();
    }
}
