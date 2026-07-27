<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\{Alert, MaintenanceTicket, Robot, RobotStatusHistory, SensorData};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed', description: 'Creates an idempotent demonstration fleet')]
final class SeedCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->em->getRepository(Robot::class)->count([]) > 0) {
            $output->writeln('Fleet already seeded.');
            return Command::SUCCESS;
        }

        $robots = [
            new Robot('PDA-2026-001', 'PDA Pasteur A', 'MediDose X4', 'CHU Pasteur', 'Lille', 'Pharmacie centrale · Zone A', '4.8.2'),
            new Robot('PDA-2026-002', 'PDA Saint-Vincent', 'MediDose X4', 'Clinique Saint-Vincent', 'Lille', 'Pharmacie · Salle 2', '4.8.2'),
            new Robot('PDA-2025-014', 'PDA Huriez', 'MediDose X3', 'Hôpital Huriez', 'Lille', 'Unité de soins · Étage 3', '4.7.9'),
            new Robot('PDA-2025-009', 'PDA Calmette', 'MediDose X3', 'Hôpital Calmette', 'Lille', 'Pharmacie clinique', '4.7.9'),
            new Robot('PDA-2024-021', 'PDA Arras', 'MediDose X2', 'Centre Hospitalier', 'Arras', 'Pharmacie centrale', '4.6.5'),
            new Robot('PDA-2026-006', 'PDA Lens', 'MediDose X4', 'Centre Hospitalier', 'Lens', 'Unité logistique', '4.8.2'),
        ];
        foreach ($robots as $index => $robot) {
            $status = "online";
            if ($index===3) $status = "offline";
            if ($index===4) $status = "maintenance";
            $robot->setStatus($status);
            if ($status === 'online') $robot->markSeen(new \DateTimeImmutable('-'.($index + 1).' minutes'));
            $this->em->persist($robot);
            $this->em->persist(new RobotStatusHistory($robot, null, $status, 'Initialisation du parc'));

            for ($point = 24; $point >= 0; --$point) {
                $battery = max(8, 92 - $index * 7 - (24 - $point) * 0.7);
                $temp = 35 + $index * 1.7 + sin($point / 3) * 2;
                $load = 40 + $index * 6 + cos($point / 2) * 8;
                $this->em->persist(new SensorData($robot, $battery, $temp, $status === 'online' ? 30 + $index * 4 : 0, $load, 820 + $index * 120 + (24 - $point) * 3, 1180 + $index * 70, new \DateTimeImmutable("-{$point} minutes")));
            }
        }

        $this->em->persist(new Alert($robots[2], 'temperature', 'warning', 'Température élevée', 'La température interne dépasse le seuil préventif de 52 °C.'));
        $this->em->persist(new Alert($robots[4], 'mechanical_fault', 'critical', 'Blocage du carrousel', 'Le carrousel de préparation ne rejoint plus sa position de référence.'));
        $ticket = new MaintenanceTicket($robots[4], 'Diagnostic du carrousel', 'Contrôler le moteur, le capteur de position et procéder au recalibrage.', 'critical', new \DateTimeImmutable('+2 hours'));
        $ticket->update('planned', null);
        $this->em->persist($ticket);
        $this->em->persist(new MaintenanceTicket($robots[1], 'Maintenance préventive trimestrielle', 'Nettoyage, contrôle métrologique et test des sécurités.', 'medium', new \DateTimeImmutable('+2 days')));
        $this->em->flush();
        $output->writeln('Demonstration fleet created.');
        return Command::SUCCESS;
    }
}
