<?php

namespace App\EventListener;

use App\Entity\Review;
use App\Entity\ReviewSummary;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Review::class)]
class UpdateReviewSummary
{
    private $em;

    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function postPersist(Review $review, PostPersistEventArgs $event): void
    {
        $summaryRepository = $this->em->getRepository(ReviewSummary::class);
        $reviewRepository = $this->em->getRepository(Review::class);
        $summary = $summaryRepository->findOneByProduct($review->getProduct()->getId());

        if ($summary) {
            $setNote = "setNote" . $review->getNote();
            $count = $reviewRepository->countReviewByNoteAndProduct($review->getNote(), $review->getProduct());

            $summary->$setNote($count["count"]);
            $this->em->flush();
        }

    }
}
