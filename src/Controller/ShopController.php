<?php

namespace App\Controller;

use App\Repository\ShopRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1')]
class ShopController extends AbstractController
{
    #[Route('/premium', name: 'app_shop', methods: Request::METHOD_GET)]
    public function index(ShopRepository $shopRepository, Request $request): JsonResponse
    {

        $shopName = $request->get('shop');
        $shop = $shopRepository->findOneBy(['name' => $shopName]);

        if ($shop) {
            $premium = $shop->isIsABuyer() ?? false;
            return new JsonResponse($premium, Response::HTTP_OK);
        }
        
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
