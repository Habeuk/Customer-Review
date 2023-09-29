<?php

namespace App\Controller;

use App\Api\ShopifyApiGateway;
use App\Entity\Shop;
use Doctrine\ORM\EntityManagerInterface;
use Shopify\Auth\FileSessionStorage;
use Shopify\Auth\OAuth;
use Shopify\Context;
use Shopify\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Shopify\Auth\OAuthCookie;

class AuthController extends AbstractController
{
    #[Route('api/oauth/install', name: 'app_install')]
    public function index(Request $request): Response
    {
        $hmac = $request->get("hmac");
        $shop = $request->get('shop');
        $key = $this->getParameter('shopify_api_key');
        $secret = $this->getParameter('shopify_api_secret');
        $scope = $this->getParameter('shopify_app_scope');
        $host = $this->getParameter('host');

        $nonce = "hello";
        $redirectUri = 'https://' . $host . $this->generateUrl('app_auth_redirect');

        $shopifyRedirect = "https://$shop/admin/oauth/authorize?client_id=$key&scope=$scope&redirect_uri=$redirectUri&state=$nonce&grant_options[]=$scope";



        $response = new RedirectResponse($shopifyRedirect,);
        $response->headers->setCookie(Cookie::create($nonce, $nonce, domain: "$shop.myshopify.com"));

        return $response;
    }

    #[Route('api/oauth/redirect', name: 'app_auth_redirect')]
    public function action(Request $request, HttpClientInterface $client, EntityManagerInterface $em): Response
    {
        $hmac = $request->get("hmac");

        $key = $this->getParameter('shopify_api_key');
        $secret = $this->getParameter('shopify_api_secret');

        $params = $request->query->all();
        $params = array_diff_key($params, array('hmac' => ''));
        ksort($params);

        $computedHmac = hash_hmac('sha256', http_build_query($params), $secret);
        if (hash_equals($hmac, $computedHmac)) {
            $accessTokenUrl = "https://" . $params['shop'] . "/admin/oauth/access_token";
            $data = [
                'client_id' => $key,
                'client_secret' => $secret,
                'code' => $params['code'],
            ];
            $response = $client->request(
                Request::METHOD_POST,
                $accessTokenUrl,
                [
                    'body' => $data,
                ]
            );

            $content = $response->toArray();
            $token = $content["access_token"];

            $shop = new Shop();

            $shop->setName($params['shop']);
            $shop->setToken($token);

            $em->persist($shop);
            $em->flush();

            $host = $request->get('host');

            $embeddedUrl = Utils::getEmbeddedAppUrl($host);

            return $this->redirect($embeddedUrl);
        }

        return new Response('', Response::HTTP_BAD_REQUEST);
    }
}
