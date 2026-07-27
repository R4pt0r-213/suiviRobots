<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class TestController
{
    #[Route('/api/test', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'message' => 'API Symfony fonctionnelle',
            'time' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ]);
    }
}
