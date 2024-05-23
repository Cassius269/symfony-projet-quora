<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/user', name: 'user_')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class UserController extends AbstractController
{
    #[Route('/{id}', name: 'profile')]
    public function showUserProfile(User $user): Response
    {
        if ($user == $this->getUser()) {
            return $this->redirectToRoute('user_currentprofile');
        }

        return $this->render('user/userprofile.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/', name: 'currentprofile')]
    public function showCurrentUserProfile(): Response
    {
        $user = $this->getUser();
        return $this->render('user/currentprofile.html.twig', [
            'user' => $user
        ]);
    }
}
