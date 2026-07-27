<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Concerns\HasUuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(columns: ['status'], name: 'IDX_ROBOT_STATUS')]
class Robot
{
    use HasUuid;

    #[ORM\Column(length: 80, unique: true)]
    private string $serialNumber;
    #[ORM\Column(length: 120)]
    private string $name;
    #[ORM\Column(length: 100)]
    private string $model;
    #[ORM\Column(length: 180)]
    private string $facilityName;
    #[ORM\Column(length: 100)]
    private string $facilityCity;
    #[ORM\Column(length: 180)]
    private string $location;
    #[ORM\Column(length: 30)]
    private string $status = 'offline';
    #[ORM\Column(length: 40)]
    private string $firmwareVersion;
    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $installedAt;
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastSeenAt = null;
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;
    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;
    /** @var Collection<int, SensorData> */
    #[ORM\OneToMany(mappedBy: 'robot', targetEntity: SensorData::class)]
    private Collection $sensorData;

    public function __construct(string $serialNumber, string $name, string $model, string $facilityName, string $facilityCity, string $location, string $firmwareVersion)
    {
        $this->initializeUuid();
        $this->serialNumber = $serialNumber;
        $this->name = $name;
        $this->model = $model;
        $this->facilityName = $facilityName;
        $this->facilityCity = $facilityCity;
        $this->location = $location;
        $this->firmwareVersion = $firmwareVersion;
        $this->installedAt = new \DateTimeImmutable();
        $this->createdAt = $this->updatedAt = new \DateTimeImmutable();
        $this->sensorData = new ArrayCollection();
    }

    public function getId(): string { return $this->id; }
    public function getSerialNumber(): string { return $this->serialNumber; }
    public function getName(): string { return $this->name; }
    public function getModel(): string { return $this->model; }
    public function getFacilityName(): string { return $this->facilityName; }
    public function getFacilityCity(): string { return $this->facilityCity; }
    public function getLocation(): string { return $this->location; }
    public function getStatus(): string { return $this->status; }
    public function getFirmwareVersion(): string { return $this->firmwareVersion; }
    public function getInstalledAt(): \DateTimeImmutable { return $this->installedAt; }
    public function getLastSeenAt(): ?\DateTimeImmutable { return $this->lastSeenAt; }
    public function setStatus(string $status): void { $this->status = $status; $this->updatedAt = new \DateTimeImmutable(); }
    public function markSeen(\DateTimeImmutable $at): void { $this->lastSeenAt = $at; $this->status = 'online'; $this->updatedAt = new \DateTimeImmutable(); }
}
