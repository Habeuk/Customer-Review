<?php

namespace App\Controller;

use App\Service\SubscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Shopify\Clients\Graphql;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/v1')]
class SubscriptionController extends AbstractController
{
    #[Route('/subscription', name: 'app_subscription', methods: Request::METHOD_GET)]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        if ($_SERVER["APP_ENV"] == "dev") {
            $shop = "madok-co.myshopify.com";
        } else if ($_SERVER["APP_ENV"] = "prod") {
            $shop = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_HOST);
        }

        $subscriptonManager = new SubscriptionManager($em);
        $confirmationUrl = $subscriptonManager->getSubscriptionUrl($shop);
        return new JsonResponse(["confirmationUrl" => $confirmationUrl], Response::HTTP_OK);
    }
}
