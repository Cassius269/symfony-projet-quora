<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public static         $questions = [
        [
            'id' => 1,
            'title' => 'Journée de la Terre',
            'content' => 'Quel est jour de la Terrre ? Quel est jour de la Terrre ?Quel est jour de la Terrre ?Quel est jour de la Terrre ?',
            'author' => [
                'name' => 'Sophie Noé',
                'imageProfile' => 'https://randomuser.me/api/portraits/women/85.jpg'
            ],
            'upvote' => 0,
            'downvote' => 20,
            'nbrOfResponses' => 4,
        ],
        [
            'id' => 2,
            'title' => 'MAC',
            'content' => 'Quel est le meilleur Mac du marché ?',
            'author' => [
                'name' => 'Hector Booba',
                'imageProfile' => 'https://randomuser.me/api/portraits/men/70.jpg'
            ],
            'upvote' => 50,
            'downvote' => 5,
            'nbrOfResponses' => 8,
        ],
        [
            'id' => 3,
            'title' => 'Disc dur',
            'content' => 'Quel disc dur prendre pour débutants ?',
            'author' => [
                'name' => 'Annaël Guy',
                'imageProfile' => 'https://randomuser.me/api/portraits/men/60.jpg'
            ],
            'upvote' => 10,
            'downvote' => 10,
            'nbrOfResponses' => 5,
        ],
        [
            'id' => 4,
            'title' => 'Programmation informatique',
            'content' => 'Comment se former de nos jours au developpement web ?',
            'author' => [
                'name' => 'Serge Laurent',
                'imageProfile' => 'https://randomuser.me/api/portraits/men/94.jpg'
            ],
            'upvote' => 50,
            'downvote' => 0,
            'nbrOfResponses' => 10,
        ]
    ];

    #[Route('/', name: 'home')]
    public function index(): Response
    {



        return $this->render('home/index.html.twig', [
            'questions' => HomeController::$questions
        ]);
    }
}
