<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $handle = null;

    #[ORM\OneToOne(mappedBy: 'handle', cascade: ['persist', 'remove'])]
    private ?ReviewSummary $reviewSummary = null;

    #[ORM\OneToMany(mappedBy: 'handle', targetEntity: Review::class)]
    private Collection $reviews;

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

    public function getReviewSummary(): ?ReviewSummary
    {
        return $this->reviewSummary;
    }

    public function setReviewSummary(ReviewSummary $reviewSummary): static
    {
        // set the owning side of the relation if necessary
        if ($reviewSummary->getHandle() !== $this) {
            $reviewSummary->setHandle($this);
        }

        $this->reviewSummary = $reviewSummary;

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
            $review->setHandle($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getHandle() === $this) {
                $review->setHandle(null);
            }
        }

        return $this;
    }
}
