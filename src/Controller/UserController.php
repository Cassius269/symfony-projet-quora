<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Scalar\MagicConst\Dir;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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

        return $this->render('user/userProfile.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/', name: 'currentprofile')]
    public function showCurrentUserProfile(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
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
            ->add('newPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
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



            /* Debut traitement de modification d'image
            $fullName = ($user->getFirstName() . $user->getLastName()) . '.jpeg';
           // rechercher le fichier si il n'existe pas
            dump(in_array('FahamiMOHAMED ALI.jpeg', scandir(__DIR__ . '/../../public/images/')));
            // si le fichier est trouvé, le remplacer le nouveau

            */

            $newPassword = $user->getNewPassword();

            if ($newPassword) {
                $hashedNewPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedNewPassword);
            }

            $user->setUpdatedAt(new DateTime());
            $em->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour');
        }

        return $this->render('user/currentProfile.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route(
        path: '/delete/{id}',
        name: "deleting"
    )]
    public function removeUser(User $user, EntityManagerInterface $em, Security $security): Response
    {
        $this->denyAccessUnlessGranted('POST_DELETE', $user);

        if ($this->getUser()->getUserIdentifier() === $user->getUserIdentifier()) {
            // Supprimer aussi les données liées à son auteur: questions, réponses, photo de profil
            $em->remove($user);
            $em->flush();
        } else {
            throw $this->createNotFoundException("Vous n'avez pas le droit supprimer ce profil");
        }

        $response = $security->logout(false);

        return $response;
    }
}
