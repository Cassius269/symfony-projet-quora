<?php

namespace App\Controller;

use App\Repository\QuestionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('IS_AUTHENTICATED_REMEMBERED')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(QuestionRepository $questionRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // Tri des questions par la plus récente à la plus ancienne
        // $questions = $questionRepository->getQuestionsWithAuthor();

        // Mise en place de la pagination
        $data = $questionRepository->findBy([], [
            'title' => 'desc'
        ]);

        //dd($questions->count());
        $questions = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            4
        );


        return $this->render('home/index.html.twig', [
            //'questions' => HomeController::$questions,
            "questions" => $questions
        ]);
    }
}
