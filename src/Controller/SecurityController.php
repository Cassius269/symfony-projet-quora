<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/inscription', name: 'inscription')]
    public function subscribe(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $profilImage ='https://randomuser.me/api/portraits/men/'.rand(1,99).'.jpg';
            $plainTextpassword = $user->getPassword();
            $user->setPassword($passwordHasher->hashPassword($user, $plainTextpassword));

            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setRoles(['ROLE_USER']);

           $user->setImageProfile($profilImage);
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Vous avez été enregistré');
        } else {
            dump('pas ok');
            $this->addFlash('error', 'Vous n\avez pas été enregistré');
        }

        return $this->render('security/inscription.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/login', name: 'login')]
    public function connect(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $username = $authenticationUtils->getLastUsername();



        return $this->render('security/login.html.twig', [
            'error' => $error,
            'username' => $username
        ]);
    }

    #[Route('logout', 'logout')]
    public function logout()
    {
    }
}
