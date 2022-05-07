<?php

declare(strict_types=1);

namespace App\Entity;

class Token
{
    private int $id;
    private string $accessToken;
    private string $refreshToken;
    private \DateTime $expirationTime;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Token
    {
        $this->id = $id;
        return $this;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): Token
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): Token
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    public function getExpirationTime(): \DateTime
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(\DateTime $expirationTime): Token
    {
        $this->expirationTime = $expirationTime;
        return $this;
    }
}
