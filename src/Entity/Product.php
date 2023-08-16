<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $handle = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Review::class)]
    private Collection $reviews;

    #[ORM\OneToOne(mappedBy: 'product', cascade: ['persist', 'remove'])]
    #[Groups(['shop:review:read'])]
    private ?ReviewSummary $reviewSummary = null;

    #[ORM\Column(length: 255)]
    #[Groups(['shop:review:read'])]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Shop $shop = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['shop:review:read'])]
    private ?string $imageSrc = null;

    public function __construct()
    {
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHandle(): ?string
    {
        return $this->handle;
    }

    public function setHandle(string $handle): static
    {
        $this->handle = $handle;

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setProduct($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getProduct() === $this) {
                $review->setProduct(null);
            }
        }

        return $this;
    }

    public function getReviewSummary(): ?ReviewSummary
    {
        return $this->reviewSummary;
    }

    public function setReviewSummary(ReviewSummary $reviewSummary): static
    {
        // set the owning side of the relation if necessary
        if ($reviewSummary->getProduct() !== $this) {
            $reviewSummary->setProduct($this);
        }

        $this->reviewSummary = $reviewSummary;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): static
    {
        $this->shop = $shop;

        return $this;
    }

    public function getImageSrc(): ?string
    {
        return $this->imageSrc;
    }

    public function setImageSrc(?string $imageSrc): static
    {
        $this->imageSrc = $imageSrc;

        return $this;
    }
}
