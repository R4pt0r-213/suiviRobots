<?php

declare(strict_types=1);

namespace App\Entity\Concerns;

use Doctrine\ORM\Mapping as ORM;

trait HasUuid
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    private function initializeUuid(): void
    {
        $this->id = bin2hex(random_bytes(16));
    }

    public function getId(): string
    {
        return $this->id;
    }
}
