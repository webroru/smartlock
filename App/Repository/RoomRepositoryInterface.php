<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Room;

interface RoomRepositoryInterface
{
    public function add(Room $room): int;
    public function find($id): ?Room;
    public function update(Room $room): void;
    public function findBy(array $params): array;
    public function findByNumber(string $number): ?Room;
    public function getMainRoom(): Room;
    public function getAll(): array;
    public function delete($id): void;
}
