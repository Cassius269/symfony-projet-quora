<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Question;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use function PHPUnit\Framework\isEmpty;

#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class QuestionController extends AbstractController
{
    #[Route(
        path: '/question/ask',
        name: 'question_askForm'
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
                $question->setUser($this->getUser());
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
        path: '/question/{id}',
        name: 'question_detail'
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
                "content" => $content,
                "question" => $question->getId()
            ]);

            if ($commentBDD) { // dans le cas où une question similaire existe, montrer un message flash et relancer la page
                $this->addFlash("erreur", "Une réponse similaire existe sur cette question");

                return $this->redirect(
                    $request->getUri()
                );
            }
            $comment->setCreatedAt(new \DateTimeImmutable());
            $comment->setUser($this->getUser());
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

    #[Route(path: "question/rating/{id}/{score}", name: 'question_rating')]
    public function ratingQuestion(Question $question, int $score, EntityManagerInterface $em, Request $request): response
    {
        $question->setRating($question->getRating() + $score);
        $referer = $request->server->get('HTTP_REFERER'); // $referer fait réferrence à l'URL de la requête précédente, en gros la page d'avant
        $em->flush();
        return $referer ?  $this->redirect($referer) : $this->redirectToRoute('home');
    }

    #[Route(path: "comment/rating/{id}/{score}", name: 'comment_rating')]
    public function ratingComment(Comment $comment, int $score, EntityManagerInterface $em, Request $request): response
    {
        $comment->setRating($comment->getRating() + $score);
        $referer = $request->server->get('HTTP_REFERER');
        $em->flush();
        return $referer ?  $this->redirect($referer) : $this->redirectToRoute('home');
    }


    #[Route(
        path: '{id}/questions',
        name: 'userQuestions'
    )]
    public function showQuestionsOfUser(User $user): Response
    {
        $getQuestions = $user->getQuestions();
        $questions = [];
        foreach ($getQuestions as $question) {
            $questions[] = $question;
        }

        return $this->render('question/questionsOfuser.html.twig', [
            'questions' => $questions,
        ]);
    }
}
