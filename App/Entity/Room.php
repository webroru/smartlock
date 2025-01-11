<?php

namespace App\Entity;

class Room
{
    private int $id;
    private string $number;
    private ?int $lockId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;
        return $this;
    }

    public function getLockId(): ?int
    {
        return $this->lockId;
    }

    public function setLockId(?int $lockId): self
    {
        $this->lockId = $lockId;
        return $this;
    }
}
