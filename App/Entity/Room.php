<?php

namespace App\Entity;

class Room
{
    private int $id;
    private string $number;
    private string $lockId;

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

    public function getLockId(): string
    {
        return $this->lockId;
    }

    public function setLockId(string $lockId): self
    {
        $this->lockId = $lockId;
        return $this;
    }
}
