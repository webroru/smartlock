<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Lock;

interface LockRepositoryInterface
{
    public function add(Lock $lock);
    public function find($id): ?Lock;
    public function update(Lock $lock): void;
    public function findBy(array $params): array;
    public function delete($id): void;
    public function getExpired(): array;
}
