<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Booking;

interface BookingRepositoryInterface
{
    public function add(Booking $booking): int;
    public function find(int $id): ?Booking;
    public function update(Booking $booking): void;
    public function findBy(array $params): array;
    public function delete(int $id): void;
}
