<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Room;

interface RoomRepositoryInterface
{
    public function add(Room $token);
    public function find($id): ?Room;
    public function update(Room $token): void;
    public function findBy(array $params): array;
    public function delete($id): void;
}
