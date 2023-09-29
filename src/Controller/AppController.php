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
    #[Route('/app', name: 'app_app')]
    public function index(Request $request): Response
    {
        if (Context::$IS_EMBEDDED_APP &&  $request->get("embedded", false) === "1") {
            return $this->redirect($this->generateUrl("app_admin"));
        } else {
            $params = $request->query->all();
            return $this->redirect($this->generateUrl("app_install"). "?" . http_build_query($params));
        }
    }
}
