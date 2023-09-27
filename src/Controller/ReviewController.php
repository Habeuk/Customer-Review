<?php

namespace App\Controller;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Review;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Repository\ReviewSummaryRepository;
use App\Repository\ShopRepository;
use App\Service\ReviewManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[ApiResource()]
#[Route('api/v1')]
class ReviewController extends AbstractController
{
    #[Route('/reviews', name: 'app_review', methods: Request::METHOD_GET)]
    public function index(
        ReviewRepository $reviewRepository,
        SerializerInterface $serializer,
        Request $request,
        ReviewSummaryRepository $summaryRepository,
        ProductRepository $productRepository,
        ShopRepository $shopRepository,
        EntityManagerInterface $em,
        CacheInterface $cache
    ): Response {
        $page = $request->get("page", 1);
        $note = $request->get("note");
        $handle = $request->get("handle");
        $minify = $request->get("minify");

        $reviewManager = new ReviewManager(
            $shopRepository,
            $em,
            $productRepository,
            $serializer,
            $summaryRepository,
            $reviewRepository,
            $cache
        );

        if ($_SERVER["APP_ENV"] == "dev") {
            $shop = "madok-co.myshopify.com";
        } else if ($_SERVER["APP_ENV"] = "prod") {
            $shop = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_HOST);
        }


        $product = $reviewManager->getProduct($handle, $shop);
        if ($product) {

            $jsonReviews = $reviewManager->getReviews($page, $note, $handle, $product->getShop(), $minify);
            if ($jsonReviews) {
                return new JsonResponse($jsonReviews, Response::HTTP_OK, ['accept' => 'json'], true);
            }
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/reviews', name: 'app_review_add', methods: Request::METHOD_POST)]
    public function add(EntityManagerInterface $em, Request $request, ValidatorInterface $validator, SerializerInterface $serializer, ProductRepository $productRepository): Response
    {
        $review = new Review();
        $data = $request->request->all();

        $review->setTitle($data["title"]);
        $review->setDescription($data["description"]);
        $review->setNote((int) $data["note"]);
        $review->setLikes(0);
        $review->setDislikes(0);

        $product = $productRepository->findOneByHandle($data["handle"]);

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
    }

    #[Route('/reviews/{id}', name: 'app_review_edit', methods: Request::METHOD_PUT)]
    public function update(Review $currentReview, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ProductRepository $productRepository): Response
    {
        $updatedReview = $serializer->deserialize(
            $request->getContent(),
            Review::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $currentReview,
                'groups' => ['review:write'],
            ]
        );
        $em->persist($updatedReview);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/reviews/{id}', name: 'app_review_get', methods: Request::METHOD_GET)]
    public function get(Review $review, SerializerInterface $serializer): JsonResponse
    {
        if ($review) {

            $jsonReviews = $serializer->serialize($review, 'json', ['groups' => 'review:read']);
            return new JsonResponse($jsonReviews, Response::HTTP_OK, ['accept' => 'json'], true);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/like/{id}', name: 'app_review_like')]
    public function like(Review $review, EntityManagerInterface $em, Request $request): Response
    {
        $reset = $request->get("reset");
        if ($reset) {
            $review->setLikes($review->getLikes() - 1);
        } else {
            $review->setLikes($review->getLikes() + 1);
        }
        $em->flush();
        
        return new JsonResponse(['ok' => true], Response::HTTP_OK);
    }

    #[Route('/dislike/{id}', name: 'app_review_like')]
    public function dislike(Review $review, EntityManagerInterface $em, Request $request): Response
    {
        $reset = $request->get("reset");
        if ($reset) {
            $review->setDislikes($review->getDislikes() - 1);
        } else {
            $review->setDislikes($review->getDislikes() + 1);
        }
        $em->flush();
        
        return new JsonResponse(['ok' => true], Response::HTTP_OK);
    }
}
