<?php

namespace App\Controller;

use Shopify\Context;
use Shopify\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    #[Route('/', name: 'app_app')]
    public function index(Request $request): Response
    {
        if (Context::$IS_EMBEDDED_APP &&  $request->get("embedded", false) === "1") {
            /* if (env('APP_ENV') === 'production') {
                return file_get_contents(public_path('index.html'));
            } else {
                return file_get_contents(base_path('frontend/index.html'));
            } */
            return new Response("hello");
        } else {
            $params = $request->query->all();
            return $this->redirect($this->generateUrl("app_install"). "?" . http_build_query($params));
        }
    }
}
