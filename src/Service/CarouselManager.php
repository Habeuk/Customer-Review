<?php

namespace App\Service;

use App\Entity\Carousel;
use App\Entity\Review;
use App\Entity\Shop;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CarouselManager
{
    private EntityManagerInterface $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function addReviews($shopName, $reviews)
    {
        $shop = $this->em->getRepository(Shop::class)->findOneBy(['name' => $shopName]);
        $carousel = $shop->getCarousel();
        if ($carousel) {
            foreach ($reviews as $review) {
                $carousel->addReview($review);
            }
            return $carousel;
        }

        $carousel = new Carousel();
        $carousel->setShop($shop);
        foreach ($reviews as $review) {
            $carousel->addReview($review);
        }
        $this->em->persist($carousel);
        $this->em->flush();

        return $carousel;
    }

    public function getReviews($shopName)
    {

        $shop = $this->em->getRepository(Shop::class)->findOneBy(['name' => $shopName]);
        if ($shop) {
            $carousel = $shop->getCarousel();
        if ($carousel) {
            return $carousel->getReview()->toArray();
        }
        $carousel = new Carousel();
        $carousel->setShop($shop);
        $this->em->persist($carousel);
        $this->em->flush();
        return null;
        }
    }
}
