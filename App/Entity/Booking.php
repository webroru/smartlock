<?php

namespace App\Entity;

class Booking
{
    private int $id;
    private string $name;
    private \DateTime $checkInDate;
    private \DateTime $checkOutDate;
    private ?string $phone;
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
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
