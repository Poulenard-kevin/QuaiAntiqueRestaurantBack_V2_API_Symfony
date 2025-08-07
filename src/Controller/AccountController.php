<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountController extends AbstractController
{
    #[Route('/api/account/me', name: 'account_me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function me(SerializerInterface $serializer): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = $serializer->serialize($user, 'json', ['groups' => ['user:read']]);
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/api/account/edit', name: 'account_edit', methods: ['PUT', 'PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        SerializerInterface $serializer
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (isset($data['firstName'])) $user->setFirstName($data['firstName']);
        if (isset($data['lastName'])) $user->setLastName($data['lastName']);
        if (isset($data['guestNumber'])) $user->setGuestNumber($data['guestNumber']);
        if (isset($data['allergy'])) $user->setAllergy($data['allergy']);
        if (isset($data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }
        if (method_exists($user, 'setUpdatedAt')) {
            $user->setUpdatedAt(new \DateTimeImmutable());
        }

        $em->flush();

        $json = $serializer->serialize($user, 'json', ['groups' => ['user:read']]);
        return new JsonResponse($json, 200, [], true);
    }
}