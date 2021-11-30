<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ImageRepository;
use App\Controller\UploadImageAction;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 * @Vich\Uploadable()
 */
#[ApiResource(
    collectionOperations: [
        "get",
        "post" => [
            "method" => "POST",
            "path" => "/images",
            "controller" => UploadImageAction::class,
            'deserialize' => false,
            "defaults" => ["_api_receive=false"]
        ],
    ]
)]
class Image
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Vich\UploadableField(mapping="images", fileNameProperty="url")
     * @var File|null
     */
    // #[Assert\NotNull()]
    private $file;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTimeInterface|null
     */
    private $updatedAt;


    /**
     * @ORM\Column(type="string", length=255, nullable = true)
     */
    private $url;

    public function getId(): ?int
    {
        return $this->id;
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
        return '/images/' . $this->url;
    }


    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the value of updatedAt
     *
     * @return  \DateTimeInterface|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set the value of updatedAt
     *
     * @param  \DateTimeInterface|null  $updatedAt
     *
     * @return  self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
