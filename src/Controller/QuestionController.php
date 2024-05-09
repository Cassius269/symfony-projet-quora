<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Le repertoire de la classe Question
        $questionRepository = $entityManager->getRepository(Question::class);

        // Création d'une nouvelle entité question
        $question = new Question();

        $form = $this->createForm(QuestionType::class, $question);

        $form->handleRequest($request);

        // Persister et flush à la base de donnée la question si elle est correcte à la soumission
        if ($form->isSubmitted() && $form->isValid()) {
            $searchQuestion = $questionRepository->findByTitle($question->getTitle());
            if ($searchQuestion) {
                $this->addFlash("erreur", "Cette question existe déjà");
            } else {
                $question->setCreatedAt(new \DateTimeImmutable());
                $question->setNbrOfResponses(0);
                $question->setRating(0);
                $entityManager->persist($question);
                $entityManager->flush();
                $this->addFlash("erreur", "Votre question a été enregistrée avec succès");
                return $this->redirectToRoute("home");
            }
        }

        return $this->render("question/index.html.twig", [
            "askForm" => $form
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
