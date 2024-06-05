<?php

namespace App\Controller;

use App\Entity\User;
use App\Event\NewUserEvent;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SecurityController extends AbstractController
{
    #[Route('/inscription', name: 'inscription')]
    public function subscribe(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, EventDispatcherInterface $eventDispatcher): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user)
            ->add('imageProfile', FileType::class, [
                'mapped' => false,
                'attr' => [
                    'accept' => 'images/*'
                ],
                'required' => true
            ])
            ->remove('submit')
            ->remove('newPassword');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nameFile = trim(($user->getFirstName()) . ($user->getLastName()), '-');
            $file = $form['imageProfile']->getData();


            if (in_array($file->getMimeType(), ["image/jpeg", "image/jpg", "image/png"])) {
                $file->move('images', $nameFile . '.jpeg');
                $plainTextpassword = $user->getPassword();
                $user->setPassword($passwordHasher->hashPassword($user, $plainTextpassword));

                $user->setCreatedAt(new \DateTimeImmutable());
                $user->setRoles(['ROLE_USER']);

                $user->setImageProfile('images/' . $nameFile . '.jpeg');
                $em->persist($user);
                $em->flush();
                $this->addFlash('success', 'Vous avez été enregistré');

                // Déclenchement de l'évenement d'envoi de mail à chaque nouvel utilisateur
                $eventDispatcher->dispatch(new NewUserEvent($user));

                return $this->redirectToRoute('login');
            } else {
                $this->addFlash('error', 'Vous n\avez pas été enregistré');
                $this->addFlash('error', "Le fichier n'est pas au bon format : choisissez par exemple JPEG, JPG");
            }
        }

        return $this->render('security/inscription.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/login', name: 'login')]
    public function connect(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

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
