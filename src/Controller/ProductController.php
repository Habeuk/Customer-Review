<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\ShopRepository;
use App\Service\ProductManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

class ProductController extends AbstractController
{
    #[Route('/shopify/admin/api/v1/products', name: 'app_product')]
    public function index(
        ShopRepository $shopRepository, 
        CacheInterface $cacheInterface,
        EntityManagerInterface $em,
        ProductRepository $productRepository,
        Request $request,
        ): Response
    {
        $produtManager = new ProductManager($shopRepository, $cacheInterface, $em, $productRepository);
        $shop = $request->get('shop');

        $products = $produtManager->getProducts($shop);

        if (!empty($products['products'])) {
            return new JsonResponse($products['products'], Response::HTTP_OK, ['accept' => 'json']);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
