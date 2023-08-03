<?php

namespace App\EventListener;

use App\Entity\Product;
use App\Entity\ReviewSummary;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Product::class)]
class CreateReviewSummary
{
    private $em;

    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function postPersist(Product $product, PostPersistEventArgs $event): void
    {
        $summary = new ReviewSummary();
        $summary->setProduct($product);
        $this->em->persist($summary);
        $this->em->flush();
    }
}
