<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api/restaurant', name: 'app_api_restaurant_')]
#[OA\Tag(name: 'Restaurants')]
class RestaurantController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly RestaurantRepository $repository,
        private readonly SerializerInterface $serializer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        path: '/api/restaurant',
        summary: 'Créer un restaurant',
        security: [['X-AUTH-TOKEN' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Mon Restaurant'),
                    new OA\Property(property: 'description', type: 'string', example: "Description du restaurant"),
                ],
                required: ['nom', 'adresse']
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Restaurant créé',
                headers: [
                    new OA\Header(header: 'Location', description: 'URL du restaurant créé', schema: new OA\Schema(type: 'string'))
                ],
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Nom du restaurant'),
                        new OA\Property(property: 'description', type: 'string', example: "Description du restaurant"),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Données invalides'),
            new OA\Response(response: 401, description: 'Non autorisé (token manquant ou invalide)'),
        ]
    )]
    public function new(Request $request): JsonResponse
    {
        $restaurant = $this->serializer->deserialize($request->getContent(), Restaurant::class, 'json');
        $restaurant->setCreatedAt(new \DateTimeImmutable());

        $this->manager->persist($restaurant);
        $this->manager->flush();

        $location = $this->urlGenerator->generate(
            name: 'app_api_restaurant_show',
            parameters: ['id' => $restaurant->getId()],
            referenceType: UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new JsonResponse(
            $this->serializer->serialize($restaurant, 'json'),
            Response::HTTP_CREATED,
            ["Location" => $location],
            true
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/restaurant/{id}',
        summary: 'Afficher un restaurant par ID',
        security: [['X-AUTH-TOKEN' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID du restaurant à afficher',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Restaurant trouvé avec succès',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Nom du restaurant'),
                        new OA\Property(property: 'description', type: 'string', example: "Description du restaurant"),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Non autorisé'),
            new OA\Response(response: 404, description: 'Restaurant non trouvé')
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $restaurant = $this->repository->find($id);
        if (!$restaurant) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $responseData = $this->serializer->serialize($restaurant, 'json');
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/restaurant/{id}',
        summary: 'Modifier un restaurant',
        security: [['X-AUTH-TOKEN' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID du restaurant à modifier',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Nouveau non du restaurant'),
                    new OA\Property(property: 'description', type: 'string', example: "Nouvelle description du restaurant"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 204, description: 'Restaurant modifié'),
            new OA\Response(response: 400, description: 'Données invalides'),
            new OA\Response(response: 401, description: 'Non autorisé'),
            new OA\Response(response: 404, description: 'Restaurant non trouvé'),
        ]
    )]
    public function edit(int $id, Request $request): JsonResponse
    {
        $restaurant = $this->repository->find($id);
        if (!$restaurant) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $this->serializer->deserialize(
            $request->getContent(),
            Restaurant::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $restaurant]
        );
        $restaurant->setUpdatedAt(new \DateTimeImmutable());
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/restaurant/{id}',
        summary: 'Supprimer un restaurant',
        security: [['X-AUTH-TOKEN' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'ID du restaurant à supprimer',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Restaurant supprimé'),
            new OA\Response(response: 401, description: 'Non autorisé'),
            new OA\Response(response: 404, description: 'Restaurant non trouvé'),
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $restaurant = $this->repository->find($id);
        if (!$restaurant) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $this->manager->remove($restaurant);
        $this->manager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}