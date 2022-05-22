<?php

namespace App\Entity;

use App\Services\LockService;

class Booking
{
    private int $id;
    private string $name;
    private \DateTime $checkInDate;
    private \DateTime $checkOutDate;
    private string $email;
    private ?string $code = null;
    private string $orderId;
    private string $property;
    private ?Lock $lock = null;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCheckInDate(): \DateTime
    {
        return $this->checkInDate;
    }

    public function setCheckInDate(\DateTime $checkInDate): self
    {
        $this->checkInDate = $checkInDate;
        return $this;
    }

    public function getCheckOutDate(): \DateTime
    {
        return $this->checkOutDate;
    }

    public function setCheckOutDate(\DateTime $checkOutDate): self
    {
        $this->checkOutDate = $checkOutDate;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail($email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function setProperty(string $property): self
    {
        $this->property = $property;
        return $this;
    }

    public function getLock(): ?Lock
    {
        return $this->lock;
    }

    public function setLock(Lock $lock): self
    {
        $this->lock = $lock;
        return $this;
    }
}
