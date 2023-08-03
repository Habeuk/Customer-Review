<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestReviewController extends AbstractController
{
    #[Route('/test/review', name: 'app_test_review')]
    public function index(ProductRepository $productRepository , EntityManagerInterface $em): Response
    {
        $review = new Review();
        $product = $productRepository->find(1);
        $review->setNote(3);
        $review->setReview("A beautiful review");
        $review->setTitle("Cool");
        $review->setProduct($product);

        $em->persist($review);
        $em->flush();
        return $this->render('test_review/index.html.twig', [
            'controller_name' => 'TestReviewController',
        ]);
    }
}
