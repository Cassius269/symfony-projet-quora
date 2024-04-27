<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route(path: '/question', name: 'question_')]
class QuestionController extends AbstractController
{
    #[Route(
        path: '/ask',
        name: 'askForm'
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
            //dd($form->getData());
            //array_push(HomeController::$questions, $form->getData());
        }
        // Rendre la vue du formulaire
        return $this->render('question/index.html.twig', [
            'askForm' => $form->createView()
        ]);
    }

    #[Route(
        path: '/{id}',
        name: 'detail'
    )]
    public function showQuestionDetail(int $id)
    {
        // stocker la variable question 
        $questions = HomeController::$questions;

        // verifier si le paramètre numérique entré appartient au tableau des questions
        $questionsById = array_column($questions, 'id');

        $result = in_array($id, $questionsById, $strict = true);

        // Si ok retourner la vue de la question en détail
        // sinon rediriger l'utilisateur avec un code d'erreur 404
        //dd($questions[$id]]);
        if ($result != true) {
            throw $this->createNotFoundException('L\'ID entré est invalide et indispobible dans le tableau des questions');
        }
        return $this->render('question/detailledQuestion.html.twig', ["question" => $questions[$id - 1]]);
    }
}
