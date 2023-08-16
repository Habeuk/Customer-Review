<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('admin/shopify', name: 'app_admin', requirements:['url' => '^.+'])]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('admin/shopify/{page}', name: 'app_admin_pages', requirements:['url' => '^.+'])]
    public function page(): Response
    {
        return $this->render('admin/index.html.twig');
    }

}
