<?php

namespace App\Service;

use App\Entity\Carousel;
use App\Entity\Shop;
use Doctrine\ORM\EntityManagerInterface;

class CarouselManager 
{
    private EntityManagerInterface $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function addReviews($shopName, $reviews){
        $shop = $this->em->getRepository(Shop::class)->findOneBy(['name' => $shopName]);
        $carousel = $shop->getCarousel();
        if ($carousel) {
            foreach ($reviews as $review) {
                $carousel->addReview($review);
            }
        } else {
            $carousel = new Carousel();
            $carousel->setShop($shop);
            foreach ($reviews as $review) {
                $carousel->addReview($review);
            }
            $this->em->persist($carousel);
        }
        $this->em->flush();

        return $carousel;
    }
}
