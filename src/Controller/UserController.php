<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function showCurrentUserProfile(Request $request, EntityManagerInterface $em, PasswordHasherInterface $passwordHasher=null): Response
    {
        $user = $this->getUser();


    
        // Création du formulaire
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Test du formulaire
        if($form->isSubmitted() && $form->isValid()){
            $plainPassword = $user->getPassword();

            $isTheSamePassword = $passwordHasher->isPasswordValid($user, $plainPassword);
            dd($isTheSamePassword);

            if($isTheSamePassword == true){
                $this->addFlash('error','Le mot de passe saisi est identitique');
            }else {
                    $hashedPassword= $passwordHasher->hashPassword($user, $plainPassword);

                    $user->setPassword($hashedPassword);
            }

            $user->setUpdatedAt(new DateTime());
            $em->flush();

            $this->addFlash("success",'Mise à jour du profil effectué');
        }
        // Updating en base de données

        return $this->render('user/currentprofile.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);


    }
}
