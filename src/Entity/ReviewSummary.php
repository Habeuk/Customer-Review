<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Product;
use App\Repository\ReviewSummaryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewSummaryRepository::class)]
class ReviewSummary
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $note1 = null;

    #[ORM\Column]
    private ?int $note2 = null;

    #[ORM\Column]
    private ?int $note3 = null;

    #[ORM\Column]
    private ?int $note4 = null;

    #[ORM\Column]
    private ?int $note5 = null;

    #[ORM\OneToOne(inversedBy: 'reviewSummary', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?product $handle = null;

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

    public function getHandle(): ?product
    {
        return $this->handle;
    }

    public function setHandle(product $handle): static
    {
        $this->handle = $handle;

        return $this;
    }
}
