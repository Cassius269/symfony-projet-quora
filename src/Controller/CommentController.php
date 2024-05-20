<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class CommentController extends AbstractController
{
    #[Route('{id}/comments', name: 'commentsOfUser')]
    public function index(User $user): Response
    {
        $commentsSearch = $user->getComments();
        $comments = [];

        foreach ($commentsSearch as $comment) {
            $comments[] = $comment;
        }

        return $this->render('comment/showCommentsOfUser.html.twig', [
            'comments' => $comments
        ]);
    }
}
