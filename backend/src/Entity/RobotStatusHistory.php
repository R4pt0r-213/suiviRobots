<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RobotStatusHistory
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'bigint')]
    private ?int $id = null;
    #[ORM\ManyToOne, ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Robot $robot;
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $previousStatus;
    #[ORM\Column(length: 30)]
    private string $newStatus;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reason;
    #[ORM\Column]
    private \DateTimeImmutable $changedAt;

    public function __construct(Robot $robot, ?string $previousStatus, string $newStatus, ?string $reason)
    {
        $this->robot = $robot;
        $this->previousStatus = $previousStatus;
        $this->newStatus = $newStatus;
        $this->reason = $reason;
        $this->changedAt = new \DateTimeImmutable();
    }

    public function getPreviousStatus(): ?string { return $this->previousStatus; }
    public function getNewStatus(): string { return $this->newStatus; }
    public function getReason(): ?string { return $this->reason; }
    public function getChangedAt(): \DateTimeImmutable { return $this->changedAt; }
}
