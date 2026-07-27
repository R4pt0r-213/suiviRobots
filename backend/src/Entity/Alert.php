<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concerns\HasUuid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(columns: ['robot_id', 'triggered_at'], name: 'IDX_ALERT_ROBOT_DATE')]
class Alert
{
    use HasUuid;

    #[ORM\ManyToOne, ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Robot $robot;
    #[ORM\Column(length: 60)]
    private string $type;
    #[ORM\Column(length: 20)]
    private string $severity;
    #[ORM\Column(length: 160)]
    private string $title;
    #[ORM\Column(type: 'text')]
    private string $message;
    #[ORM\Column(length: 20)]
    private string $status = 'open';
    #[ORM\Column]
    private \DateTimeImmutable $triggeredAt;
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $acknowledgedAt = null;
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resolvedAt = null;

    public function __construct(Robot $robot, string $type, string $severity, string $title, string $message)
    {
        $this->initializeUuid();
        $this->robot = $robot;
        $this->type = $type;
        $this->severity = $severity;
        $this->title = $title;
        $this->message = $message;
        $this->triggeredAt = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }
    public function getRobot(): Robot { return $this->robot; }
    public function getType(): string { return $this->type; }
    public function getSeverity(): string { return $this->severity; }
    public function getTitle(): string { return $this->title; }
    public function getMessage(): string { return $this->message; }
    public function getStatus(): string { return $this->status; }
    public function getTriggeredAt(): \DateTimeImmutable { return $this->triggeredAt; }
    public function acknowledge(): void { $this->status = 'acknowledged'; $this->acknowledgedAt = new \DateTimeImmutable(); }
    public function resolve(): void { $this->status = 'resolved'; $this->resolvedAt = new \DateTimeImmutable(); }
}
