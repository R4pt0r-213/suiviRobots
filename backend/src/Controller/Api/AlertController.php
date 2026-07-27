<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Alert;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request};
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/alerts')]
final class AlertController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $criteria = [];
        foreach (['status', 'severity'] as $filter) if ($request->query->has($filter)) $criteria[$filter] = $request->query->getString($filter);
        $alerts = $this->em->getRepository(Alert::class)->findBy($criteria, ['triggeredAt' => 'DESC'], min(200, $request->query->getInt('limit', 50)));
        return $this->json(['items' => array_map(fn (Alert $alert) => $this->alertToArray($alert), $alerts), 'total' => count($alerts)]);
    }

    #[Route('/{id}/acknowledge', methods: ['PATCH'])]
    public function acknowledge(Alert $alert): JsonResponse
    {
        $alert->acknowledge();
        $this->em->flush();
        return $this->json($this->alertToArray($alert));
    }

    #[Route('/{id}/resolve', methods: ['PATCH'])]
    public function resolve(Alert $alert): JsonResponse
    {
        $alert->resolve();
        $this->em->flush();
        return $this->json($this->alertToArray($alert));
    }

    private function alertToArray(Alert $alert): array
    {
        return [
            'id' => $alert->getId(),
            'robotId' => $alert->getRobot()->getId(),
            'robotName' => $alert->getRobot()->getName(),
            'type' => $alert->getType(),
            'severity' => $alert->getSeverity(),
            'title' => $alert->getTitle(),
            'message' => $alert->getMessage(),
            'status' => $alert->getStatus(),
            'triggeredAt' => $alert->getTriggeredAt()->format(DATE_ATOM),
        ];
    }
}
