<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class QuestionController extends AbstractController
{
    #[Route(
        path: '/question/ask',
        name: 'asqQuestion'
    )]
    public function index(Request $request): Response
    {
        // Créer une nouvelle instance de l'objet question 
        $question = new Question();

        // Créer le formulaire
        $form = $this->createForm(QuestionType::class, $question);

        // Reccuillir la requete
        $form->handleRequest($request);

        // Traiter le formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            dd($form->getData());
        }
        // Rendre la vue du formulaire
        return $this->render('question/index.html.twig', [
            'askForm' => $form->createView()
        ]);
    }
}
