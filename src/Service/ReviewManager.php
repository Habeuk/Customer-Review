<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Repository\ReviewSummaryRepository;
use App\Repository\ShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Shopify\Auth\FileSessionStorage;
use Shopify\Clients\Rest;
use Shopify\Context;
use Symfony\Component\Serializer\SerializerInterface;

class ReviewManager
{
    private ShopRepository $shopRepository;
    private ProductRepository $productRepository;
    private EntityManagerInterface $em;
    private SerializerInterface $serializer;
    private ReviewSummaryRepository $summaryRepository;
    private ReviewRepository $reviewRepository;

    function __construct(
        ShopRepository $shopRepository,
        EntityManagerInterface $em,
        ProductRepository $productRepository,
        SerializerInterface $serializer,
        ReviewSummaryRepository $summaryRepository,
        ReviewRepository $reviewRepository,
    ) {
        $this->shopRepository = $shopRepository;
        $this->productRepository = $productRepository;
        $this->summaryRepository = $summaryRepository;
        $this->reviewRepository = $reviewRepository;
        $this->serializer = $serializer;
        $this->em = $em;
        Context::initialize(
            apiKey: $_ENV['SHOPIFY_API_KEY'],
            apiSecretKey: $_ENV['SHOPIFY_API_SECRET'],
            scopes: $_ENV['SHOPIFY_APP_SCOPES'],
            hostName: $_ENV['SHOPIFY_APP_HOST_NAME'],
            sessionStorage: new FileSessionStorage('/tmp/php_sessions'),
            apiVersion: '2023-04',
            isEmbeddedApp: true,
            isPrivateApp: false,
        );
    }

    public function getProduct(string $handle, string $shopName): ?Product
    {
        $shop = $this->shopRepository->findOneBy(['name' => $shopName]);
        if ($shop) {
            $product = $this->productRepository->findOneByShopAndHandle($handle, $shop);
            if ($product) {
                return $product;
            } else {
                $client = new Rest($shop->getName(), $shop->getToken());

                $response = $client->get(path: 'products');
                $products = $response->getDecodedBody();
                if (key_exists('products', $products)) {
                    foreach ($products["products"] as $product) {
                        if ($product['handle'] == $handle) {
                            $p = new Product();
                            $p->setHandle($product["handle"]);
                            $p->setTitle($product["title"]);
                            $p->setShop($shop);
                            $this->em->persist($p);
                            $this->em->flush();
                            return $p;
                        }
                    }
                }
            }
        }

        return null;
    }

    public function getReviews($page, $note, $handle, $shop)
    {
        $reviews = $this->reviewRepository->findReviews($page, $note, $handle, $shop);
        if ($reviews) {
            $jsonReviews = $this->serializer->serialize($reviews, 'json', ['groups' => 'review:read']);
            $reviews = json_decode($jsonReviews);

            foreach ($reviews as $review) {
                $review->created_at = strtotime($review->created_at);

                foreach ($review->reponse as $reponse) {
                    $reponse->created_at = strtotime($reponse->created_at);
                }

                $review->reponse = "";
            }
            $result["review"] = $reviews;
            $product = $this->productRepository->findOneByHandle($handle);
            if ($product) {
                $summary = $this->summaryRepository->findOneByProduct($product);
                $summaryArray = [
                    'note_1' => $summary->getNote1(),
                    'note_2' => $summary->getNote2(),
                    'note_3' => $summary->getNote3(),
                    'note_4' => $summary->getNote4(),
                    'note_5' => $summary->getNote5(),
                ];
                $result["summary"] = $summaryArray;
            }
            return json_encode($result);
        }
    }
}
