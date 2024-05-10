<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Question;
use App\Form\CommentType;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function PHPUnit\Framework\isEmpty;

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
    public function showQuestionDetail(Question $question, Request $request, EntityManagerInterface $em): Response
    {
        // Récuperer le repértoire des réponses
        $commentRepository = $em->getRepository(comment::class);

        //Créeer le formulaire
        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);

        // Récueillir la requête 
        $form->handleRequest($request);

        // Verifier les données soumis à la réception du formulaire et envoyer les données à la BDD si ok
        if ($form->isSubmitted() && $form->isValid()) { // dans le cas où le formulaire est dsoumis et valide face aux contraintes
            $content = $comment->getContent();

            $commentBDD = $commentRepository->findOneBy([
                "content" => $content
            ]);

            if ($commentBDD) { // dans le cas où une question similaire montrer un message flash et relancer la page
                $this->addFlash("erreur", "Une réponse similaire existe sur cette question");

                return $this->redirect(
                    $request->getUri()
                );
            }

            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setQuestion($question);
            $comment->setRating(0);
            $em->persist($comment);
            $em->flush();

            $this->addFlash("success", "Votre réponse a été ajoutée avec succès");
            return $this->redirect(
                $request->getUri()
            );
        }

        return $this->render('question/detailledQuestion.html.twig', [
            "question" => $question,
            "commentForm" => $form->createView(),
        ]);
    }
}
