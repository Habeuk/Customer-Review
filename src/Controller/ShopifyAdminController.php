<?php

namespace App\Controller;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Product;
use App\Entity\Review;
use App\Repository\CarouselRepository;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Repository\ShopRepository;
use App\Service\CarouselManager;
use App\Service\ProductManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[Route('/shopify/admin/api/v1')]
class ShopifyAdminController extends AbstractController
{
    #[Route('/reviews', name: 'app_shopify_admin', methods: Request::METHOD_GET)]
    public function index(
        ReviewRepository $reviewRepository,
        Request $request,
        ShopRepository $shopRepository,
        SerializerInterface $serializer
    ): Response {
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

    #[Route('/reviews', name: 'app_shop_product_add', methods: Request::METHOD_POST)]
    public function add(
        EntityManagerInterface $em,
        Request $request,
        ValidatorInterface
        $validator,
        SerializerInterface $serializer,
        ProductRepository $productRepository,
        ShopRepository $shopRepository,
        CacheInterface $cache,
    ): Response {

        if ($_SERVER["APP_ENV"] == "dev") {
            $shopName = "madok-co.myshopify.com";
        } else if ($_SERVER["APP_ENV"] = "prod") {
            $shopName = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_HOST);
        }

        $review = new Review();
        $data = json_decode($request->getContent(), true);

        $review->setTitle($data["title"]);
        $review->setName($data["name"]);
        $review->setEmail($data["email"]);
        $review->setDescription($data["description"]);
        $review->setNote((int) $data["note"]);
        $review->setLikes(0);
        $review->setDislikes(0);

        $productManager = new ProductManager($shopRepository, $cache, $em, $productRepository);

        $product = $productManager->getProduct($data["handle"], $shopName);

        if ($product) {
            $review->setProduct($product);

            $errors = $validator->validate($review);

            if (count($errors) > 0) {
                /*
         * Uses a __toString method on the $errors variable which is a
         * ConstraintViolationList object. This gives us a nice string
         * for debugging.
         */
                $errorsString = (string) $errors;

                return new Response($errorsString);
            }

            $em->persist($review);
            $em->flush();

            $jsonReviews = $serializer->serialize($review, 'json', ['groups' => 'review:read']);
            return new JsonResponse($jsonReviews, Response::HTTP_OK, ['accept' => 'json'], true);
        } else {
            return new Response("Product does not exist");
        }
    }

    #[Route('/carousel', name: 'app_shop_carousel_add', methods: Request::METHOD_POST)]
    public function addToCarousel(
        EntityManagerInterface $em, 
        Request $request,
        SerializerInterface $serializer, 
        ): Response
    {

        if ($_SERVER["APP_ENV"] == "dev") {
            $shopName = "madok-co.myshopify.com";
        } else if ($_SERVER["APP_ENV"] = "prod") {
            $shopName = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_HOST);
        }

        $data = $data = json_decode($request->getContent(), true);
        $ids = [];
        foreach ($data as $review) {
            $ids[] = $review['id'];
        }
        $reviews = $em->getRepository(Review::class)->findBy([
            'id' => $ids
        ]);

        $carouselManager = new CarouselManager($em);
        $carousel = $carouselManager->addReviews($shopName, $reviews);

        $jsonReviews = $serializer->serialize($reviews, 'json', ['groups' => 'review:read']);
        return new JsonResponse($jsonReviews, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
