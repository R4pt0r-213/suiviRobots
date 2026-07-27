<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concerns\HasUuid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MaintenanceTicket
{
    use HasUuid;

    #[ORM\ManyToOne, ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Robot $robot;
    #[ORM\Column(length: 180)]
    private string $title;
    #[ORM\Column(type: 'text')]
    private string $description;
    #[ORM\Column(length: 20)]
    private string $priority;
    #[ORM\Column(length: 30)]
    private string $status = 'open';
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $scheduledAt;
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $resolutionNotes = null;
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;
    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct(Robot $robot, string $title, string $description, string $priority, ?\DateTimeImmutable $scheduledAt = null)
    {
        $this->initializeUuid();
        $this->robot = $robot;
        $this->title = $title;
        $this->description = $description;
        $this->priority = $priority;
        $this->scheduledAt = $scheduledAt;
        $this->createdAt = $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }
    public function getRobot(): Robot { return $this->robot; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getPriority(): string { return $this->priority; }
    public function getStatus(): string { return $this->status; }
    public function getScheduledAt(): ?\DateTimeImmutable { return $this->scheduledAt; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function update(string $status, ?string $notes): void
    {
        $this->status = $status;
        $this->resolutionNotes = $notes;
        $this->updatedAt = new \DateTimeImmutable();
        if ($status === 'in_progress' && !$this->startedAt) $this->startedAt = new \DateTimeImmutable();
        if ($status === 'completed') $this->completedAt = new \DateTimeImmutable();
    }
}
