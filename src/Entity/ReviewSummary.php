<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Product;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ReviewSummaryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    description: "The review summary for a specific product",
    shortName: "summary",
    operations: [
        new Get(),
    ],
    normalizationContext: [
        'groups' => ['summary:read'],
    ]
)]
#[ORM\Entity(repositoryClass: ReviewSummaryRepository::class)]
class ReviewSummary
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['summary:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['summary:read'])]
    private ?int $note1 = 0;

    #[ORM\Column]
    #[Groups(['summary:read'])]
    private ?int $note2 = 0;

    #[ORM\Column]
    #[Groups(['summary:read'])]
    private ?int $note3 = 0;

    #[ORM\Column]
    #[Groups(['summary:read'])]
    private ?int $note4 = 0;

    #[ORM\Column]
    #[Groups(['summary:read'])]
    private ?int $note5 = 0;

    #[ORM\OneToOne(inversedBy: 'reviewSummary', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNote1(): ?int
    {
        return $this->note1;
    }

    public function setNote1(int $note1): static
    {
        $this->note1 = $note1;

        return $this;
    }

    public function getNote2(): ?int
    {
        return $this->note2;
    }

    public function setNote2(int $note2): static
    {
        $this->note2 = $note2;

        return $this;
    }

    public function getNote3(): ?int
    {
        return $this->note3;
    }

    public function setNote3(int $note3): static
    {
        $this->note3 = $note3;

        return $this;
    }

    public function getNote4(): ?int
    {
        return $this->note4;
    }

    public function setNote4(int $note4): static
    {
        $this->note4 = $note4;

        return $this;
    }

    public function getNote5(): ?int
    {
        return $this->note5;
    }

    public function setNote5(int $note5): static
    {
        $this->note5 = $note5;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
