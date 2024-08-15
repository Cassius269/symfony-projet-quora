<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use App\Entity\User;
use App\Entity\Token;
use App\Form\UserType;
use DateTimeImmutable;
use App\Event\UserUpdatedEvent;
use App\Repository\UserRepository;
use App\Event\UserResetPasswordEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as EventDispatcherEventDispatcherInterface;

#[Route(name: 'user_')]
class UserController extends AbstractController
{
    #[Route('user/{id}', name: 'profile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function showUserProfile(User $user): Response
    {
        if ($user == $this->getUser()) {
            return $this->redirectToRoute('user_currentprofile');
        }

        return $this->render('user/userProfile.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/user', name: 'currentprofile')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function showCurrentUserProfile(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, EventDispatcherInterface $eventDispatcher): Response
    {
        /** @var \App\Entity\User */
        $user = $this->getUser();

        // Création du formulaire personnalisé
        $form = $this->createForm(UserType::class, $user)
            ->remove('password')
            ->remove('newPassword')
            ->add('imageProfile', FileType::class, [
                'mapped' => false,
                'attr' => [
                    'accept' => 'images/*'
                ],
                'required' => false
            ]);

        $form->handleRequest($request);

        // Test du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUpdatedAt(new DateTime());
            $em->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour');
            $eventDispatcher->dispatch(new UserUpdatedEvent($user));
        }

        return $this->render('user/currentProfile.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route(
        path: '/user/delete/{id}',
        name: "deleting"
    )]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function removeUser(User $user, EntityManagerInterface $em, Security $security): Response
    {
        $this->denyAccessUnlessGranted('USER_DELETE', $user);

        // Supprimer aussi les données liées à son auteur: questions, réponses, photo de profil
        $em->remove($user);
        $em->flush();

        $response = $security->logout(false);

        return $response;
    }

    // Action permettant de créer un token de réinitialisation de mot de passe
    #[Route(
        path: '/reset-password',
        name: 'resetPassword'
    )]
    public function resetPassword(RateLimiterFactory $passwordRecoveryLimiter, Request $request, UserRepository $userRepository,  EventDispatcherEventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager): Response
    {
        // Limiter le nombre de tentative de saisies de mails
        $limiter = $passwordRecoveryLimiter->create($request->getClientIp());
        // Tester si toutes les tentatives ont été consommés: 4 tentatives possibles
        if (false === $limiter->consume(1)->isAccepted()) {
            $this->addFlash('error', 'Vous devez attendre une heure pour réssayer');
            return $this->redirectToRoute('login');
        }

        // Verifier si l'utilisateur est existant avec le contenu du formulaire à l'aide de son mail
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData();

            // Recherche de l'utilisateur à partir du mail enregistré
            $user = $userRepository->findOneBy(
                [
                    'email' => $email,
                ]
            );

            if ($user) { // si utilisateur trouvé, déclencher l'évenement d'envoi de mail réinitilisation de mot de passe avec un token
                dump($user);
                $today = new Datetime();
                $expiryDate = $today->add(new DateInterval('PT1H'));
                $token = new Token();
                $token->setUser($user)
                    ->setToken(bin2hex(random_bytes(15)))
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setExpiryDate($expiryDate);

                $entityManager->persist($token);
                $entityManager->flush();

                $eventDispatcher->dispatch(new UserResetPasswordEvent($user, $token));
            };
            $this->addFlash('success', 'Un email de réinitialisation vous a été envoyé par mail');
        }

        // Récuperation du token
        // si le token est valid, stoker le token en BDD et envoyer un mail avec le token caché dans le bouton reset


        return $this->render('security/resetPassword.html.twig', [
            'form' => $form->createView()
        ]);
    }

    // L'action permettant de modifier le mdp de l'utilisater
    #[Route(
        path: '/resetPasswordByEmail/{token}',
        name: 'resetPasswordByEmail'
    )]
    public function resetPasswordByEmail(RateLimiterFactory $passwordRecoveryLimiter, #[MapEntity(mapping: ['token' => 'token'])] Token $token, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Limiter le nombre de tentative de saisies de mails
        $limiter = $passwordRecoveryLimiter->create($request->getClientIp());
        // Tester si toutes les tentatives ont été consommés: 4 tentatives possibles
        if (false === $limiter->consume(1)->isAccepted()) {
            $this->addFlash('error', 'Vous devez attendre une heure pour réssayer');
            return $this->redirectToRoute('login');
        }

        if (!$token) {
            $this->addFlash('error', 'Le token n\'existe pas, veuillez refaire la demande');
        }

        $user = $token->getUser();

        //dd($user->getPassword());
        if ($token->getExpiryDate() > new DateTime()) { // si le token n'est pas expiré, permettre à l'utilisateur de modifier le mot de passe

            $form = $this->createForm(UserType::class, $user)
                ->remove('firstname')
                ->remove('lastname')
                ->remove('newPassword')
                ->remove('email');

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // dd('Le token n\'est pas expiré');

                $newPassword = $form["password"]->getData();
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);

                $user->setPassword($hashedPassword);
                $user->setUpdatedAt(new DateTime());
                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été mise à jour');

                $tokens = $entityManager->getRepository(Token::class)->findBy(
                    [
                        'user' => $user
                    ]
                );
                // dd($tokens);
                if ($tokens) { // si l'utilisateur a des tokens en BDD, les supprimer après reset du mot de passe
                    foreach ($tokens as $registredToken) {
                        $entityManager->remove($registredToken);
                        $entityManager->flush();
                    }
                    $entityManager->flush();
                }

                return $this->redirectToRoute('login');
            }
        } else {
            $entityManager->remove($token);
            $entityManager->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('security/resetPassword.html.twig', [
            'form' => $form
        ]);
    }
}
