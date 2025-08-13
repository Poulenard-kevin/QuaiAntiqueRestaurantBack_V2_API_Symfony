<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'app_api_')]
class SecurityController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly SerializerInterface $serializer
    ) {}

    #[Route('/registration', name: 'registration', methods: ['POST'])]
    #[OA\Post(
        path: '/api/registration',
        summary: 'Inscription d\'un nouvel utilisateur',
        requestBody: new OA\RequestBody(
            description: 'Données de l\'utilisateur à inscrire',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'adresse@email.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'Mot de passe'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utilisateur inscrit avec succès',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'username', type: 'string', example: 'Nom d\'utilisateur'),
                        new OA\Property(property: 'api_token', type: 'string', example: '31a023e212f116124a36af14ea0c1c3806eb9378'),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string', example: 'ROLE_USER'))
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Erreur de validation ou de désérialisation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'error', type: 'string', example: 'Le mot de passe est obligatoire.')
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        try {
            $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        } catch (ExceptionInterface $e) {
            return new JsonResponse(
                ['error' => 'Données invalides : ' . $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!$user->getPassword()) {
            return new JsonResponse(
                ['error' => 'Le mot de passe est obligatoire.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setCreatedAt(new DateTimeImmutable());

        $this->manager->persist($user);
        $this->manager->flush();

        return new JsonResponse(
            [
                'username' => $user->getUserIdentifier(),
                'api_token' => $user->getApiToken(),
                'roles' => $user->getRoles()
            ],
            Response::HTTP_CREATED
        );
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    #[OA\Post(
        path: '/api/login',
        summary: 'Connexion d\'un utilisateur',
        requestBody: new OA\RequestBody(
            description: 'Données de l\'utilisateur à inscrire',
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'adresse@email.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'Mot de passe'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Connexion réussie',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'username', type: 'string', example: 'adresse@email.com'),
                        new OA\Property(property: 'api_token', type: 'string', example: "31a0415588458g84587431a0415588458g845874"),
                        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: 'ROLE_USER')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Identifiants manquants',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'missing credentials')
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'missing credentials'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'user' => $user->getUserIdentifier(),
            'api_token' => $user->getApiToken(),
            'roles' => $user->getRoles(),
        ]);
    }
}