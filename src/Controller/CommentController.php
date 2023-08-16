<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Review;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommentController extends AbstractController
{
    #[Route('/comments/{id}', name: 'app_comment_ad', methods: Request::METHOD_POST)]
    public function add(
        Review $review, 
        EntityManagerInterface $em, 
        Request $request, 
        ValidatorInterface $validator, 
        SerializerInterface $serializer, 
        ProductRepository $productRepository
        ): Response
    {
        $comment = new Comment();
        $data = json_decode($request->getContent(), true);

        $comment->setComment($data["comment"]);

        $comment->setReview($review);

        $em->persist($comment);
        $em->flush();

        $jsonReviews = $serializer->serialize($review, 'json', ['groups' => 'review:read']);
        return new JsonResponse($jsonReviews, Response::HTTP_OK, ['accept' => 'json'], true);
    }
}
