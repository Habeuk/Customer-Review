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
            $total = $reviewRepository->countReviewsByProduct($review->getProduct());

            $summary->$setNote($count["count"]);
            $summary->setTotal($total["count"]);
            $summary->setMean($this->getMean($total["count"], $summary));
            $this->em->flush();
        }
    }

    public function getMean(int $total, ReviewSummary $summary)
    {
        $note = 0;
        $note += $summary->getNote1() * 1;
        $note += $summary->getNote2() * 2;
        $note += $summary->getNote3() * 3;
        $note += $summary->getNote4() * 4;
        $note += $summary->getNote5() * 5;

        if ($total == 0 || $note == 0) {
            return 0;
        }

        $mean = $note / $total;

        return $mean * 100 / 5;
    }
}
