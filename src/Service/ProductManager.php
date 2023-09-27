<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\ShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Shopify\Auth\FileSessionStorage;
use Shopify\Clients\Rest;
use Shopify\Context;
use Symfony\Contracts\Cache\CacheInterface;

class ProductManager
{

    private ShopRepository $shopRepository;
    private CacheInterface $cache;
    private ProductRepository $productRepository;
    private EntityManagerInterface $em;

    function __construct(
        ShopRepository $shopRepository,
        CacheInterface $cache,
        EntityManagerInterface $em,
        ProductRepository $productRepository
    ) {
        $this->shopRepository = $shopRepository;
        $this->cache = $cache;
        $this->productRepository = $productRepository;
        $this->em = $em;
        Context::initialize(
            apiKey: $_ENV['SHOPIFY_API_KEY'],
            apiSecretKey: $_ENV['SHOPIFY_API_SECRET'],
            scopes: $_ENV['SHOPIFY_APP_SCOPES'],
            hostName: $_ENV['SHOPIFY_APP_HOST_NAME'],
            sessionStorage: new FileSessionStorage('/tmp/php_hopNamesessions'),
            apiVersion: '2023-04',
            isEmbeddedApp: true,
            isPrivateApp: false,
        );
    }

    public function getProducts(string $shopName)
    {
        $shop = $this->shopRepository->findOneBy(['name' => $shopName]);
        $productsCache = $this->cache->getItem('product_list_' . $shopName);
        if ($productsCache->isHit()) {
            return $productsCache->get();
        } else {
            $client = new Rest($shop->getName(), $shop->getToken());

            $response = $client->get(path: 'products');
            $products = $response->getDecodedBody();

            $productsCache->expiresAfter(300);
            $this->cache->save($productsCache->set($products));
            return $products;
        }
    }

    public function getProduct($handle, $shopName)
    {
        $shop = $this->shopRepository->findOneBy(['name' => $shopName]);
        $product = $this->productRepository->findOneByShopAndHandle($handle, $shop);
        if ($product) {
            return $product;
        }

        $client = new Rest($shop->getName(), $shop->getToken());

        $response = $client->get(path: 'products');
        $products = $response->getDecodedBody();
        if (key_exists('products', $products)) {
            foreach ($products["products"] as $product) {
                if ($product['handle'] == $handle) {
                    $p = new Product();
                    $p->setHandle($product["handle"]);
                    $p->setTitle($product["title"]);
                    if (key_exists('featured_image', $product)) {
                        $p->setImageSrc($product["featured_image"]);
                    }
                    $p->setShop($shop);
                    $this->em->persist($p);
                    $this->em->flush();
                    return $p;
                }
            }
        }

        return null;
    }
}
