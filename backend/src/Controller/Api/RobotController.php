<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\{Alert, MaintenanceTicket, Robot, RobotStatusHistory, SensorData};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request};
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/robots')]
final class RobotController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $criteria = [];
        if ($request->query->has('status')) $criteria['status'] = $request->query->getString('status');
        $robots = $this->em->getRepository(Robot::class)->findBy($criteria, ['name' => 'ASC']);
        $items = array_map(fn (Robot $robot) => $this->robotToArray($robot, $this->latest($robot)), $robots);
        return $this->json(['items' => $items, 'total' => count($items)]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $input = $request->toArray();
        $required = ['serialNumber', 'name', 'model', 'facilityName', 'facilityCity', 'location', 'firmwareVersion'];

        $missing = [];

        foreach ($required as $field) {
            if (!isset($input[$field]) || trim((string) $input[$field]) === '') {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            return $this->json([
                'error' => 'validation_failed',
                'fields' => $missing
            ], 422);
        }
        if ($this->em->getRepository(Robot::class)->findOneBy(['serialNumber' => $input['serialNumber']])) return $this->json(['error' => 'serial_number_already_exists'], 409);

        $robot = new Robot(...array_map(fn ($key) => trim((string) $input[$key]), $required));
        $this->em->persist($robot);
        $this->em->persist(new RobotStatusHistory($robot, null, 'offline', 'Robot enregistré'));
        $this->em->flush();
        return $this->json($this->robotToArray($robot), 201);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(Robot $robot): JsonResponse
    {
        $openAlerts = $this->em->getRepository(Alert::class)->count(['robot' => $robot, 'status' => 'open']);
        return $this->json([...$this->robotToArray($robot, $this->latest($robot)), 'openAlerts' => $openAlerts]);
    }

    #[Route('/{id}/status', methods: ['PATCH'])]
    public function status(Robot $robot, Request $request): JsonResponse
    {
        $input = $request->toArray();
        $status = (string) ($input['status'] ?? '');
        if (!in_array($status, ['online', 'offline', 'maintenance'], true)) return $this->json(['error' => 'invalid_status'], 422);
        $previous = $robot->getStatus();
        $robot->setStatus($status);
        $this->em->persist(new RobotStatusHistory($robot, $previous, $status, $input['reason'] ?? null));
        $this->em->flush();
        return $this->json($this->robotToArray($robot, $this->latest($robot)));
    }

    #[Route('/{id}/telemetry', methods: ['GET'])]
    public function telemetry(Robot $robot, Request $request): JsonResponse
    {
        $limit = min(500, max(1, $request->query->getInt('limit', 60)));
        $items = $this->em->getRepository(SensorData::class)->findBy(['robot' => $robot], ['recordedAt' => 'DESC'], $limit);
        return $this->json(['items' => array_reverse(array_map(fn (SensorData $data) => $this->sensorToArray($data), $items))]);
    }

    #[Route('/{id}/timeline', methods: ['GET'])]
    public function timeline(Robot $robot): JsonResponse
    {
        $events = [];
        foreach ($this->em->getRepository(Alert::class)->findBy(['robot' => $robot], ['triggeredAt' => 'DESC'], 30) as $alert) {
            $events[] = ['type' => 'alert', 'title' => $alert->getTitle(), 'detail' => $alert->getMessage(), 'severity' => $alert->getSeverity(), 'at' => $alert->getTriggeredAt()->format(DATE_ATOM)];
        }
        foreach ($this->em->getRepository(MaintenanceTicket::class)->findBy(['robot' => $robot], ['createdAt' => 'DESC'], 30) as $ticket) {
            $events[] = ['type' => 'maintenance', 'title' => $ticket->getTitle(), 'detail' => $ticket->getStatus(), 'severity' => $ticket->getPriority(), 'at' => $ticket->getCreatedAt()->format(DATE_ATOM)];
        }
        foreach ($this->em->getRepository(RobotStatusHistory::class)->findBy(['robot' => $robot], ['changedAt' => 'DESC'], 30) as $status) {
            $events[] = ['type' => 'status', 'title' => 'Statut : '.$status->getNewStatus(), 'detail' => $status->getReason(), 'severity' => 'info', 'at' => $status->getChangedAt()->format(DATE_ATOM)];
        }
        usort($events, fn ($a, $b) => strcmp($b['at'], $a['at']));
        return $this->json(['items' => array_slice($events, 0, 50)]);
    }

    private function latest(Robot $robot): ?SensorData
    {
        return $this->em->getRepository(SensorData::class)->findOneBy(['robot' => $robot], ['recordedAt' => 'DESC']);
    }

    private function robotToArray(Robot $robot, ?SensorData $latest = null): array
    {
        return [
            'id' => $robot->getId(),
            'serialNumber' => $robot->getSerialNumber(),
            'name' => $robot->getName(),
            'model' => $robot->getModel(),
            'facility' => [
                'name' => $robot->getFacilityName(),
                'city' => $robot->getFacilityCity(),
                'location' => $robot->getLocation(),
            ],
            'status' => $robot->getStatus(),
            'firmwareVersion' => $robot->getFirmwareVersion(),
            'installedAt' => $robot->getInstalledAt()->format('Y-m-d'),
            'lastSeenAt' => $robot->getLastSeenAt()?->format(DATE_ATOM),
            'latestMetrics' => $latest ? $this->sensorToArray($latest) : null,
        ];
    }

    private function sensorToArray(SensorData $data): array
    {
        return [
            'id' => $data->getId(),
            'battery' => $data->getBatteryLevel(),
            'temperature' => $data->getInternalTemperature(),
            'speed' => $data->getActivitySpeed(),
            'systemLoad' => $data->getSystemLoad(),
            'dosesPrepared' => $data->getDosesPrepared(),
            'cycleTimeMs' => $data->getCycleTimeMs(),
            'recordedAt' => $data->getRecordedAt()->format(DATE_ATOM),
        ];
    }
}
