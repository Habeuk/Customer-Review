<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ReviewSummary;
use App\Entity\Shop;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Repository\ReviewSummaryRepository;
use App\Repository\ShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Shopify\Auth\FileSessionStorage;
use Shopify\Clients\Rest;
use Shopify\Context;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class ReviewManager
{
    private EntityManagerInterface $em;
    private SerializerInterface $serializer;
    private ReviewRepository $reviewRepository;
    private CacheInterface $cache;

    function __construct(
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        CacheInterface $cache,
    ) {
        $this->serializer = $serializer;
        $this->em = $em;
        $this->cache = $cache;
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

    public function getProduct(?string $handle, string $shopName): ?Product
    {
        $shop = $this->em->getRepository(Shop::class)->findOneBy(['domain' => $shopName]);
        if ($shop == null) {
            $shop = $this->em->getRepository(Shop::class)->findOneBy(['name' => $shopName]);
        }
        
        if ($shop) {
            $productCache = $this->cache->getItem("product_cache_" . $handle . "_" . $shopName);
            /* if ($productCache->isHit()) {
                return $productCache->get();
            } else { */
                $product = $this->em->getRepository(Product::class)->findOneByShopAndHandle($handle, $shop);
                if ($product) {
                    $productCache->expiresAfter(600);
                    $this->cache->save($productCache->set($product));
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
                                $p->setImageSrc($product["featured_image"]);
                                $p->setShop($shop);
                                $this->em->persist($p);
                                $this->em->flush();

                                $productCache->expiresAfter(600);
                                $this->cache->save($productCache->set($p));
                                return $p;
                            }
                        }
                    }
                }
            //}
        }

        return null;
    }

    public function getReviews($page, $note, $handle, $shop, $minify)
    {
        $cacheNames = $this->cache->getItem($shop->getName() . "_" . $handle);
        $name = $this->getCacheName($shop->getName(), $handle, $page, $note, $minify);
        /* if ($cacheNames->isHit()) {
            $value = $cacheNames->get();
            if (!key_exists($name, $value)) {
                $value["$name"] = $name;
                $cacheNames->expiresAfter(600);
                $this->cache->save($cacheNames->set($value));
            }
        } else {
            $this->cache->save($cacheNames->set([
                "$name" => $name
            ]));
        } */

        $pageReview = $this->cache->getItem($name);
        //if (!$pageReview->isHit()) {
            if ($minify == "1") {
                $product = $this->em->getRepository(Product::class)->findOneByShopAndHandle($handle, $shop);
                $summary = $product->getReviewSummary();
                $result = [];
                $result["mean"] = $summary->getMean();
                $result["count"] = $summary->getTotal();

                $pageReview->expiresAfter(600);
                $this->cache->save($pageReview->set(json_encode($result)));
                return json_encode($result);
            }

            $reviews = $this->reviewRepository->findReviews($shop, $page, $note, $handle);
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
                $product = $this->em->getRepository(Product::class)->findOneByHandle($handle);
                if ($product) {
                    $summary = $this->em->getRepository(ReviewSummary::class)->findOneByProduct($product);
                    $summaryArray = [
                        'note_5' => $summary->getNote5(),
                        'note_4' => $summary->getNote4(),
                        'note_3' => $summary->getNote3(),
                        'note_2' => $summary->getNote2(),
                        'note_1' => $summary->getNote1(),
                    ];
                    $result["summary"] = $summaryArray;
                }

                $pageReview->expiresAfter(600);
                $this->cache->save($pageReview->set(json_encode($result)));
                return json_encode($result);
            }
        /* } else {
            return $pageReview->get();
        } */
    }

    /**
     * Return the of the cache
     * @param string $productHandle
     * @param string $shopName
     * @param int $page
     * @param null|int $note
     * @param null|int $minify
     * @return string
     */
    public function getCacheName($productHandle, $shopName, int $page = 1, $note = null, $minify = null)
    {
        $name = $shopName . "_" . $productHandle . "_page_$page";

        if (!is_null($note)) {
            $name .= "_note_$note";
        } elseif (!is_null($minify)) {
            $name .= "_minify_$minify";
        }

        return $name;
    }
}
