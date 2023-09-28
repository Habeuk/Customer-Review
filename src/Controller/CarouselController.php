<?php

namespace App\Controller;

use App\Service\CarouselManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/v1')]
class CarouselController extends AbstractController
{
    #[Route('/carousel', name: 'app_carousel', methods: Request::METHOD_GET)]
    public function index(EntityManagerInterface $em, SerializerInterface $serializer): Response
    {
        if ($_SERVER["APP_ENV"] == "dev") {
            $shop = "madok-co.myshopify.com";
        } else if ($_SERVER["APP_ENV"] = "prod") {
            $shop = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_HOST);
        }

        $carouselManager = new CarouselManager($em);

        $reviews = $carouselManager->getReviews($shop);
        $jsonReviews = $serializer->serialize($reviews, 'json', ['groups' => 'review:read']);
        if ($jsonReviews) {

            return new JsonResponse($jsonReviews, Response::HTTP_OK, ['accept' => 'json'], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
