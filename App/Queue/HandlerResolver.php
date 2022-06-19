<?php

declare(strict_types=1);

namespace App\Queue;

use App\Queue\Handlers\HandlerInterface;

class HandlerResolver
{
    /**
     * @param HandlerInterface[] $handlers
     */
    public function __construct(
        private readonly array $handlers
    ) {
    }

    public function resolve(string $handlerName): HandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if (get_class($handler) === $handlerName) {
                return $handler;
            }
        }
        throw new \Exception("Handler '$handlerName' not found");
    }
}
