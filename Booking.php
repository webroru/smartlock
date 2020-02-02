<?php

namespace App;

class Booking
{
    private $id;
    private $name;
    private $checkInDate;
    private $checkOutDate;
    private $email;

    public function getId(): string
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
        return new \DateTime($this->checkInDate);
    }

    public function setCheckInDate(\DateTime $checkInDate): self
    {
        $this->checkInDate = $checkInDate->getTimestamp();
        return $this;
    }

    public function getCheckOutDate(): \DateTime
    {
        return new \DateTime($this->checkOutDate);
    }

    public function setCheckOutDate($checkOutDate): self
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
}
