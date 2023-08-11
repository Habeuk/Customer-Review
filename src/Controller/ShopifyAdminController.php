<?php

namespace App\Controller;

use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/shopify/admin')]
class ShopifyAdminController extends AbstractController
{
    #[Route('/reviews', name: 'app_shopify_admin')]
    public function index(ReviewRepository $reviewRepository): Response
    {

        $reviews = $reviewRepository->findReviewsByShop($shop);
        return $this->render('shopify_admin/index.html.twig', [
            'controller_name' => 'ShopifyAdminController',
        ]);
    }
}
