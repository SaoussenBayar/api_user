<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    #[Route('/users', name: 'Liste tous les utilisateurs', methods: ['GET'])]
    #[Route('/users/{id}', name: 'Affiche les détails pour un utilisateur',  methods: ['GET'])]
    #[Route('/users', name: 'Créer un nouvel utilisateur', methods: ['POST'])]
    #[Route('/users/{id}', name: 'Modifier un utilisateur', methods: ['PUT'])]
    #[Route('/users/{id}', name: 'Supprimer un utilisateur', methods: ['DELETE'])]

    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
}
