<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Review;
use App\Entity\Shop;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 *
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    //    /**
    //     * @return Review[] Returns an array of Review objects
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

    //    public function findOneBySomeField($value): ?Review
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findReviews(Shop $shop, int $page = 1, $note = null, $handle = null)
    {
        $pageSize = 10;
        $firstResult = ($page - 1) * $pageSize;

        $q = $this->createQueryBuilder('r')
            ->leftJoin('r.product','p')
            ->andWhere('p.shop = :shop')
            ->setParameter('shop', $shop)
            ->orderBy('r.id', 'DESC')
            ->setFirstResult($firstResult)
            ->setMaxResults($pageSize);

        if ($note) {
            $q->andWhere('r.note= :note')
                ->setParameter('note', $note);
        }

        if ($handle) {
            $q->andWhere('p.handle = :handle')
                ->setParameter('handle', $handle);
        }

        return $q->getQuery()
            ->getResult();
    }

    public function findReviewsByShop(int $page = 1, $shop, $isUnpublished, $isPublished)
    {
        $pageSize = 10;
        $firstResult = ($page - 1) * $pageSize;

        $q = $this->createQueryBuilder('r')
            ->leftJoin('r.product','p')
            ->orderBy('r.id', 'DESC')
            ->andWhere('p.shop = :shop')
            ->setParameter('shop', $shop)
            ->setFirstResult($firstResult)
            ->setMaxResults($pageSize);

        if ($isUnpublished) {
            $q->andWhere('r.isValidated = :val')
            ->setParameter('val', false);
        }

        if ($isPublished) {
            $q->andWhere('r.isValidated = :val')
            ->setParameter('val', true);
        }

        return $q->getQuery()
            ->getResult();
    }


    public function countReviewByNoteAndProduct($note, $product)
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id) as count')
            ->andWhere('r.note = :note')
            ->andWhere('r.product = :product')
            ->setParameter('note', $note)
            ->setParameter('product', $product)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countReviewsByProduct($product)
    {
        return $this->createQueryBuilder('r')
            ->select('count(r.id) as count')
            ->andWhere('r.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
