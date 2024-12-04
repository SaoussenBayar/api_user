<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/users', name: 'list_users', methods: ['GET'])]
    public function listUsers(UserRepository $userRepository): JsonResponse
    {
        // Récupérer tous les utilisateurs
        $users = $userRepository->findAll();
        
        // Transformer les utilisateurs en tableau pour JSON
        $data = array_map(function (User $user) {
            return [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
            ];
        }, $users);

        return $this->json($data);
    }

    #[Route('/users/{id}', name: 'get_user', methods: ['GET'])]
    public function getUser(int $id, UserRepository $userRepository): JsonResponse
    {
        // Récupérer un utilisateur par ID
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        return $this->json([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
        ]);
    }

    #[Route('/users', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setName($data['name']);
        $user->setEmail($data['email']);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'User created',
            'id' => $user->getId(),
        ], 201);
    }

    #[Route('/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $user->setName($data['name']);
   
    }

    #[Route('/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(int $id, UserRepository $userRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $userRepository->find($id);

        if (!$user) {    
            return $this->json(['message' => 'User not found'], 404);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['message' => 'User deleted']);
    }
}