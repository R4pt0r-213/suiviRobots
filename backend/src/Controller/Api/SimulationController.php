<?php

namespace App\Controller\Api;

use App\Entity\Alert;
use App\Entity\Robot;
use App\Entity\SensorData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class SimulationController
{
    #[Route('/api/simulation/tick', methods: ['POST'])]
    public function tick(EntityManagerInterface $em): JsonResponse
    {
        $robots = $em->getRepository(Robot::class)->findBy(['status' => 'online']);
        $updatedRobots = 0;

        foreach ($robots as $robot) {
            $lastData = $em->getRepository(SensorData::class)->findOneBy(
                ['robot' => $robot],
                ['recordedAt' => 'DESC']
            );

            if ($lastData) {
                $battery = $lastData->getBatteryLevel() - (mt_rand(1, 8) / 100);
                $temperature = $lastData->getInternalTemperature() + (mt_rand(-8, 8) / 10);
                $doses = $lastData->getDosesPrepared() + mt_rand(0, 3);
            } else {
                $battery = mt_rand(65, 95);
                $temperature = mt_rand(350, 430) / 10;
                $doses = 800;
            }

            if ($battery < 9) {
                $battery = 95; // on fait comme si le robot avait été rechargé
            }

            if ($temperature < 30) {
                $temperature = 30;
            }

            if ($temperature > 65) {
                $temperature = 65;
            }

            $now = new \DateTimeImmutable();
            $sensor = new SensorData(
                $robot,
                round($battery, 2),
                round($temperature, 2),
                mt_rand(25, 55),
                mt_rand(35, 82),
                $doses,
                mt_rand(1050, 1450),
                $now
            );

            $robot->markSeen($now);
            $em->persist($sensor);

            // Alertes
            if ($temperature >= 62) {
                $alreadyExists = $em->getRepository(Alert::class)->findOneBy([
                    'robot' => $robot,
                    'type' => 'overheat',
                    'status' => 'open',
                ]);

                if (!$alreadyExists) {
                    $em->persist(new Alert(
                        $robot,
                        'overheat',
                        'critical',
                        'Surchauffe détectée',
                        'Température interne à '.round($temperature, 1).' °C.'
                    ));
                }
            } elseif ($temperature >= 52) {
                $alreadyExists = $em->getRepository(Alert::class)->findOneBy([
                    'robot' => $robot,
                    'type' => 'temperature',
                    'status' => 'open',
                ]);

                if (!$alreadyExists) {
                    $em->persist(new Alert(
                        $robot,
                        'temperature',
                        'warning',
                        'Température élevée',
                        'Température interne à '.round($temperature, 1).' °C.'
                    ));
                }
            }

            if ($battery <= 25) {
                $alreadyExists = $em->getRepository(Alert::class)->findOneBy([
                    'robot' => $robot,
                    'type' => 'low_battery',
                    'status' => 'open',
                ]);

                if (!$alreadyExists) {
                    $severity = $battery <= 12 ? 'critical' : 'warning';
                    $title = $battery <= 12 ? 'Batterie critique' : 'Batterie faible';

                    $em->persist(new Alert(
                        $robot,
                        'low_battery',
                        $severity,
                        $title,
                        'Niveau de batterie à '.round($battery).'%.' 
                    ));
                }
            }

            $updatedRobots++;
        }

        $em->flush();

        return new JsonResponse([
            'updatedRobots' => $updatedRobots,
            'generatedAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ]);
    }
}
