<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\NumericFilter;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ProductRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    description: "A review added by a customer for a specific product",
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
    ],
    normalizationContext: [
        'groups' => ['review:read'],
    ],
    denormalizationContext: [
        'groups' => ['review:write'],
    ],
    paginationItemsPerPage: 10
)]
#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    public const NOTES = [1,2,3,4,5];
    public const LIKES = [0,1];


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['review:read'])]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['review:read', 'review:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, minMessage: 'Your description mus have at least 10 chars')]
    #[Groups(['review:read','review:write'])]
    private ?string $review = null;

    #[ORM\Column]
    #[Groups(['review:read'])]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\Column]
    private ?bool $isValidated = false;

    #[ORM\Column]
    #[Groups(['review:read','review:write'])]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: Review::NOTES, message: 'The note must be a number between 1 and 5')]
    #[ApiFilter(NumericFilter::class)]
    private ?int $note = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    #[ORM\Column]
    #[Groups(['review:read','review:write'])]
    #[Assert\Choice(choices: Review::LIKES, message: 'The lkes must be 0 or 5')]
    private ?int $likes = null;

    #[ORM\Column]
    #[Groups(['review:read','review:write'])]
    #[Assert\Choice(choices: Review::LIKES, message: 'The lkes must be 0 or 5')]
    private ?int $dislikes = null;

    function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getReview(): ?string
    {
        return $this->review;
    }

    public function setReview(string $review): static
    {
        $this->review = $review;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): static
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): static
    {
        $this->likes = $likes;

        return $this;
    }

    public function getDislikes(): ?int
    {
        return $this->dislikes;
    }

    public function setDislikes(int $dislikes): static
    {
        $this->dislikes = $dislikes;

        return $this;
    }

}
