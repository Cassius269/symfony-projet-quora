<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
    public function showCurrentUserProfile(Request $request, EntityManagerInterface $em, PasswordHasherInterface $passwordHasher = null): Response
    {
        $user = $this->getUser();



        // Création du formulaire personnalisé
        $form = $this->createFormBuilder($user)
            ->add('firstname', TextType::class, [
                'label' => 'Prénom :',
                'attr' => [
                    "class" => 'formProfile'
                ]
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nom :'
            ])
            ->add('email')
            ->add('imageProfile', FileType::class, [
                'mapped' => false,
                'attr' => [
                    'accept' => 'images/*'
                ],
                'required' => false
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    "class" => "buttonSubmit"
                ]
            ])
            ->getForm();

        $form->handleRequest($request);

        // Test du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            dump($user);

            $user->setUpdatedAt(new DateTime());
            $em->flush();
        }

        return $this->render('user/currentprofile.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}
