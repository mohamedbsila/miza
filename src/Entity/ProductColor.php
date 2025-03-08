<?php

namespace App\Entity;

use App\Repository\ProductColorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: ProductColorRepository::class)]
class ProductColor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'colors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column(length: 50)]
    private ?string $colorName = null;

    #[ORM\Column(length: 7)]
    private ?string $colorCode = null;

    #[ORM\Column(length: 255)]
    private ?string $imageOn = null;

    #[ORM\Column(length: 255)]
    private ?string $imageOff = null;

    #[ORM\Column]
    private ?int $sortOrder = 0;

    private $imageOnFile;
    private $imageOffFile;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function getColorName(): ?string
    {
        return $this->colorName;
    }

    public function setColorName(string $colorName): self
    {
        $this->colorName = $colorName;
        return $this;
    }

    public function getColorCode(): ?string
    {
        return $this->colorCode;
    }

    public function setColorCode(string $colorCode): self
    {
        $this->colorCode = $colorCode;
        return $this;
    }

    public function getImageOn(): ?string
    {
        return $this->imageOn;
    }

    public function setImageOn(?string $imageOn): self
    {
        $this->imageOn = $imageOn;
        return $this;
    }

    public function getImageOff(): ?string
    {
        return $this->imageOff;
    }

    public function setImageOff(?string $imageOff): self
    {
        $this->imageOff = $imageOff;
        return $this;
    }

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getImageOnFile(): ?File
    {
        return $this->imageOnFile;
    }

    public function setImageOnFile(?File $imageOnFile): self
    {
        $this->imageOnFile = $imageOnFile;
        return $this;
    }

    public function getImageOffFile(): ?File
    {
        return $this->imageOffFile;
    }

    public function setImageOffFile(?File $imageOffFile): self
    {
        $this->imageOffFile = $imageOffFile;
        return $this;
    }
} 
 