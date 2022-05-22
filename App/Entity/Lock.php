<?php

declare(strict_types=1);

namespace App\Entity;

class Lock
{
    private int $id;
    private string $passcodeId;
    private string $passcode;
    private string $name;
    private \DateTime $startDate;
    private \DateTime $endDate;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getPasscodeId(): string
    {
        return $this->passcodeId;
    }

    public function setPasscodeId(string $passcodeId): Lock
    {
        $this->passcodeId = $passcodeId;
        return $this;
    }

    public function getPasscode(): string
    {
        return $this->passcode;
    }

    public function setPasscode(string $passcode): self
    {
        $this->passcode = $passcode;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getStartDate(): \DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): \DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }
}
