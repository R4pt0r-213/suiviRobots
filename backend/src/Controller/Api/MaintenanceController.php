<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\{MaintenanceTicket, Robot};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request};
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/maintenance-tickets')]
final class MaintenanceController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $criteria = $request->query->has('status') ? ['status' => $request->query->getString('status')] : [];
        $tickets = $this->em->getRepository(MaintenanceTicket::class)->findBy($criteria, ['createdAt' => 'DESC']);
        return $this->json(['items' => array_map(fn (MaintenanceTicket $ticket) => $this->ticketToArray($ticket), $tickets)]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $input = $request->toArray();
        $robot = $this->em->getRepository(Robot::class)->find($input['robotId'] ?? '');
        if (!$robot || empty($input['title']) || empty($input['description']) || !in_array($input['priority'] ?? '', ['low', 'medium', 'high', 'critical'], true)) {
            return $this->json(['error' => 'validation_failed'], 422);
        }
        $scheduledAt = isset($input['scheduledAt']) && $input['scheduledAt'] ? new \DateTimeImmutable($input['scheduledAt']) : null;
        $ticket = new MaintenanceTicket($robot, trim($input['title']), trim($input['description']), $input['priority'], $scheduledAt);
        $this->em->persist($ticket);
        $this->em->flush();
        return $this->json($this->ticketToArray($ticket), 201);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    public function update(MaintenanceTicket $ticket, Request $request): JsonResponse
    {
        $input = $request->toArray();
        $status = $input['status'] ?? $ticket->getStatus();
        if (!in_array($status, ['open', 'planned', 'in_progress', 'completed', 'cancelled'], true)) return $this->json(['error' => 'invalid_status'], 422);
        $ticket->update($status, $input['resolutionNotes'] ?? null);
        $this->em->flush();
        return $this->json($this->ticketToArray($ticket));
    }

    private function ticketToArray(MaintenanceTicket $ticket): array
    {
        return [
            'id' => $ticket->getId(),
            'robotId' => $ticket->getRobot()->getId(),
            'robotName' => $ticket->getRobot()->getName(),
            'title' => $ticket->getTitle(),
            'description' => $ticket->getDescription(),
            'priority' => $ticket->getPriority(),
            'status' => $ticket->getStatus(),
            'scheduledAt' => $ticket->getScheduledAt()?->format(DATE_ATOM),
            'createdAt' => $ticket->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}
