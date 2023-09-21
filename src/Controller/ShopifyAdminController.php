<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Review;
use App\Repository\ReviewRepository;
use App\Repository\ShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/shopify/admin/api/v1')]
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
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/reviews/{id}', name: 'app_shop_review_get', methods: Request::METHOD_GET)]
    public function get(Review $review, SerializerInterface $serializer): JsonResponse
    {
        if ($review) {

            $jsonReviews = $serializer->serialize($review, 'json', ['groups' => 'shop:review:read']);
            return new JsonResponse($jsonReviews, Response::HTTP_OK, ['accept' => 'json'], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/product/{id}', name: 'app_shop_product_get', methods: Request::METHOD_GET)]
    public function getProduct(Review $review, SerializerInterface $serializer): JsonResponse
    {
        if ($review) {
            $product = $review->getProduct();

            $jsonProduct = $serializer->serialize($product, 'json', ['groups' => 'shop:review:read']);
            return new JsonResponse($jsonProduct, Response::HTTP_OK, ['accept' => 'json'], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
