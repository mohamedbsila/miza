<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: 'products')]
#[UniqueEntity(fields: ['sku'], message: 'This SKU is already in use.')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $originalPrice = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $discount = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $brand = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $material = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $sku = null;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    private ?Category $category = null;

    #[ORM\ManyToOne(targetEntity: Subcategory::class)]
    private ?Subcategory $subcategory = null;

    #[ORM\Column]
    private ?int $stockQuantity = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $position = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isFeatured = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isExploreProduct = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductImage::class, cascade: ['persist', 'remove'])]
    private Collection $images;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?bool $isNew = true;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ProductColor::class, cascade: ['persist', 'remove'])]
    private Collection $colors;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $finish = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $style = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $height = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $width = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $designer = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $collection = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $lamping = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $colorTemperature = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $dimming = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $inputVoltage = null;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->colors = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function addImage(ProductImage $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setProduct($this);
        }
        return $this;
    }

    public function removeImage(ProductImage $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getProduct() === $this) {
                $image->setProduct(null);
            }
        }
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;
        
        // Update originalPrice if there's a discount
        if ($this->discount !== null) {
            $currentPrice = floatval($price);
            $this->originalPrice = number_format($currentPrice / (1 - $this->discount/100), 2);
        }
        
        return $this;
    }

    public function getOriginalPrice(): ?string
    {
        return $this->originalPrice;
    }

    public function setOriginalPrice(?string $originalPrice): self
    {
        $this->originalPrice = $originalPrice;
        return $this;
    }

    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    public function setDiscount(?int $discount): self
    {
        $this->discount = $discount;
        
        // Automatically update originalPrice when discount is set
        if ($discount !== null && $this->price !== null) {
            $currentPrice = floatval($this->price);
            $this->originalPrice = number_format($currentPrice / (1 - $discount/100), 2);
        }
        
        return $this;
    }

    public function getCurrentPrice(): ?string
    {
        return $this->price;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): self
    {
        $this->sku = $sku;
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

    public function getSubcategory(): ?Subcategory
    {
        return $this->subcategory;
    }

    public function setSubcategory(?Subcategory $subcategory): self
    {
        $this->subcategory = $subcategory;
        return $this;
    }

    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    public function setStockQuantity(int $stockQuantity): self
    {
        $this->stockQuantity = $stockQuantity;
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): self
    {
        $this->isFeatured = $isFeatured;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
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

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function isIsNew(): ?bool
    {
        return $this->isNew;
    }

    public function setIsNew(bool $isNew): self
    {
        $this->isNew = $isNew;
        return $this;
    }

    public function getColors(): Collection
    {
        return $this->colors;
    }

    public function addColor(ProductColor $color): self
    {
        if (!$this->colors->contains($color)) {
            $this->colors[] = $color;
            $color->setProduct($this);
        }
        return $this;
    }

    public function removeColor(ProductColor $color): self
    {
        if ($this->colors->removeElement($color)) {
            if ($color->getProduct() === $this) {
                $color->setProduct(null);
            }
        }
        return $this;
    }

    public function getFinish(): ?string
    {
        return $this->finish;
    }

    public function setFinish(?string $finish): self
    {
        $this->finish = $finish;
        return $this;
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function setStyle(?string $style): self
    {
        $this->style = $style;
        return $this;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(?string $height): self
    {
        $this->height = $height;
        return $this;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function setWidth(?string $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function getDesigner(): ?string
    {
        return $this->designer;
    }

    public function setDesigner(?string $designer): self
    {
        $this->designer = $designer;
        return $this;
    }

    public function getCollection(): ?string
    {
        return $this->collection;
    }

    public function setCollection(?string $collection): self
    {
        $this->collection = $collection;
        return $this;
    }

    public function getLamping(): ?string
    {
        return $this->lamping;
    }

    public function setLamping(?string $lamping): self
    {
        $this->lamping = $lamping;
        return $this;
    }

    public function getColorTemperature(): ?string
    {
        return $this->colorTemperature;
    }

    public function setColorTemperature(?string $colorTemperature): self
    {
        $this->colorTemperature = $colorTemperature;
        return $this;
    }

    public function getDimming(): ?string
    {
        return $this->dimming;
    }

    public function setDimming(?string $dimming): self
    {
        $this->dimming = $dimming;
        return $this;
    }

    public function getInputVoltage(): ?string
    {
        return $this->inputVoltage;
    }

    public function setInputVoltage(?string $inputVoltage): self
    {
        $this->inputVoltage = $inputVoltage;
        return $this;
    }

    public function isExploreProduct(): bool
    {
        return $this->isExploreProduct;
    }

    public function setIsExploreProduct(bool $isExploreProduct): self
    {
        $this->isExploreProduct = $isExploreProduct;
        return $this;
    }

    public function getMainImage(): ?string
    {
        // First try to get the primary image
        foreach ($this->images as $image) {
            if ($image->getIsPrimary()) {
                return $image->getImageOn();
            }
        }
        
        // If no primary image, return the first image
        if ($this->images->count() > 0) {
            return $this->images->first()->getImageOn();
        }
        
        // Return the single image field if set
        if ($this->image) {
            return $this->image;
        }
        
        // Return a default image if nothing else is available
        return 'assets/images/default-product.jpg';
    }

    public function getBrand(): ?string
    {
        return $this->brand ?? 'MIZA';
    }

    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function getMaterial(): ?string
    {
        return $this->material;
    }

    public function setMaterial(?string $material): self
    {
        $this->material = $material;
        return $this;
    }

    public function getSelectedColor(): ?string
    {
        if ($this->colors->count() > 0) {
            return $this->colors->first()->getColorName();
        }
        return null;
    }

    public function isInStock(): bool
    {
        return $this->stockQuantity > 0;
    }

    public function getUrl(): ?string
    {
        foreach ($this->images as $image) {
            return $image->getImageOn();
        }
        return null;
    }

    /**
     * Calculate the final price after discount
     */
    public function calculateFinalPrice(): ?string
    {
        if ($this->price === null) {
            return null;
        }

        if ($this->discount === null || $this->discount === 0) {
            return $this->price;
        }

        $price = floatval($this->price);
        $discountAmount = $price * ($this->discount / 100);
        return number_format($price - $discountAmount, 2);
    }
} 