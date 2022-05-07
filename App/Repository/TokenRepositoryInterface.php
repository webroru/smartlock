<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Token;

interface TokenRepositoryInterface
{
    public function add(Token $token);
    public function find($id): ?Token;
    public function update(Token $token): void;
    public function findBy(array $params): array;
    public function delete($id): void;
}
