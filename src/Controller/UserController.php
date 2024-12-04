<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;  // <-- Correct use statement
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; 

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
                'name' => $user->getUsername(),
                'email' => $user->getEmail(),
            ];
        }, $users);

        return $this->json($data);
    }

    #[Route('/users/{id}', name: 'get_user', methods: ['GET'])]
    public function findUserById(int $id, UserRepository $userRepository): JsonResponse
    {
        // Récupérer un utilisateur par ID
        $user = $userRepository->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
        ]);
    }

    #[Route('/users', name: 'create_user', methods: ['POST'])]
    public function createUser(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator, // Injecting the validator service
        UserPasswordHasherInterface $passwordHasher // Injecting the password hasher
    ): JsonResponse {
        // 1. Get the data from the request
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Invalid JSON data'], 400);
        }

        // 2. Validate the data
        $user = new User();
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setPassword($data['password']);  // You should hash the password before saving it

        // Perform validation
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            // If there are validation errors, return them
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorsArray], 400);
        }

        // 3. Hash the password before storing it
        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        // 4. Persist the user data
        $entityManager->persist($user);
        $entityManager->flush();

        // 5. Return a success response
        return new JsonResponse([
            'message' => 'User created successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
            ]
        ], 201);
    }

    #[Route('/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // 1. Récupérer l'utilisateur
        $user = $userRepository->find($id);

        if (!$user) {
            // Retourner une réponse si l'utilisateur n'existe pas
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        // 2. Décoder les données JSON envoyées dans la requête
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            // Retourner une réponse si les données JSON sont invalides
            return new JsonResponse(['error' => 'Invalid JSON data'], 400);
        }

        // 3. Mettre à jour les champs de l'utilisateur si disponibles
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }

        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }

        // 4. Sauvegarder les modifications
        $entityManager->persist($user);
        $entityManager->flush();

        // 5. Toujours retourner une réponse JSON
        return new JsonResponse([
            'message' => 'User updated successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
            ],
        ]);
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