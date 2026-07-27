<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index(columns: ['robot_id', 'recorded_at'], name: 'IDX_SENSOR_ROBOT_DATE')]
class SensorData
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'bigint')]
    private ?int $id = null;
    #[ORM\ManyToOne(inversedBy: 'sensorData'), ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Robot $robot;
    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $batteryLevel;
    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $internalTemperature;
    #[ORM\Column(type: 'decimal', precision: 7, scale: 2)]
    private string $activitySpeed;
    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $systemLoad;
    #[ORM\Column]
    private int $dosesPrepared;
    #[ORM\Column]
    private int $cycleTimeMs;
    #[ORM\Column]
    private \DateTimeImmutable $recordedAt;

    public function __construct(Robot $robot, float $battery, float $temperature, float $speed, float $load, int $doses, int $cycleTimeMs, \DateTimeImmutable $recordedAt)
    {
        $this->robot = $robot;
        $this->batteryLevel = (string) $battery;
        $this->internalTemperature = (string) $temperature;
        $this->activitySpeed = (string) $speed;
        $this->systemLoad = (string) $load;
        $this->dosesPrepared = $doses;
        $this->cycleTimeMs = $cycleTimeMs;
        $this->recordedAt = $recordedAt;
    }

    public function getId(): ?int { return $this->id; }
    public function getBatteryLevel(): float { return (float) $this->batteryLevel; }
    public function getInternalTemperature(): float { return (float) $this->internalTemperature; }
    public function getActivitySpeed(): float { return (float) $this->activitySpeed; }
    public function getSystemLoad(): float { return (float) $this->systemLoad; }
    public function getDosesPrepared(): int { return $this->dosesPrepared; }
    public function getCycleTimeMs(): int { return $this->cycleTimeMs; }
    public function getRecordedAt(): \DateTimeImmutable { return $this->recordedAt; }
}
