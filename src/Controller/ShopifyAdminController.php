<?php

namespace App\Controller;

use App\Repository\ReviewRepository;
use App\Repository\ShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/shopify/admin')]
class ShopifyAdminController extends AbstractController
{
    #[Route('/reviews', name: 'app_shopify_admin')]
    public function index(
    ReviewRepository $reviewRepository, 
    Request $request, 
    ShopRepository $shopRepository,
    SerializerInterface $serializer
    ): Response
    {
        $page = $request->get('page', 1);
        if ($_SERVER["APP_ENV"] == "dev") {
            $shopName = "madok-co.myshopify.com";
        } else if ($_SERVER["APP_ENV"] = "prod") {
            $shopName = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_HOST);
        }

        $shop = $shopRepository->findOneBy(['name' => $shopName]);
        $isUnpublished = $request->get('unpublished') == '1' ? true : false;
        $isPublished = $request->get('published') == '1' ? true : false;

        $reviews = $reviewRepository->findReviewsByShop($page, $shop, $isUnpublished, $isPublished);
        if ($reviews) {

            $jsonReviews = $serializer->serialize($reviews, 'json', ['groups' => 'shop:review:read']);
            if ($jsonReviews) {
                return new JsonResponse($jsonReviews, Response::HTTP_OK, ['accept' => 'json'], true);
            }
        }
        dd($reviews);
        return $this->render('shopify_admin/index.html.twig', [
            'controller_name' => 'ShopifyAdminController',
        ]);
    }
}
