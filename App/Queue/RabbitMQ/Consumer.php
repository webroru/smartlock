<?php

declare(strict_types=1);

namespace App\Queue\RabbitMQ;

use App\Logger;
use App\Queue\ConsumerInterface;
use App\Queue\HandlerResolver;
use App\Queue\Job\JobInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Consumer implements ConsumerInterface
{
    public function __construct(
        private readonly AMQPStreamConnection $connection,
        private readonly HandlerResolver $resolver,
        private readonly string $queueName
    ) {
    }

    public function work(): void
    {
        $channel = $this->connection->channel();
        $channel->basic_consume(
            $this->queueName,
            callback: fn(AMQPMessage $message) => $this->handleMessage($message)
        );
        while ($channel->is_open()) {
            $channel->wait();
        }
    }

    private function handleMessage(AMQPMessage $message): void
    {
        try {
            /** @var JobInterface $job */
            $job = unserialize($message->getBody());
            $this->resolver->resolve($job->getHandlerFQCN())->handle($job);
        } catch (\Exception $e) {
            Logger::error("Can't handle the job: {$message->getBody()}");
        }
    }
}
