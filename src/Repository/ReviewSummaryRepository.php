<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ReviewSummary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReviewSummary>
 *
 * @method ReviewSummary|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReviewSummary|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReviewSummary[]    findAll()
 * @method ReviewSummary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewSummaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReviewSummary::class);
    }

    //    /**
    //     * @return ReviewSummary[] Returns an array of ReviewSummary objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ReviewSummary
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }


    public function findOneByProduct($product): ?ReviewSummary
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.product = :val')
            ->setParameter('val', $product)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
