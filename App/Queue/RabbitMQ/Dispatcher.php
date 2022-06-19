<?php

declare(strict_types=1);

namespace App\Queue\RabbitMQ;

use App\Logger;
use App\Queue\DispatcherInterface;
use App\Queue\Job\JobInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Dispatcher implements DispatcherInterface
{
    public function __construct(
        private readonly AMQPStreamConnection $connection,
        private readonly string $queueName
    ) {
        $this->connection->channel()->queue_declare($this->queueName, auto_delete: false);
    }

    public function add(JobInterface $job): void
    {
        $serializedJob = serialize($job);
        try {
            $msg = new AMQPMessage($serializedJob);
            $this->connection->channel()->basic_publish($msg, routing_key: $this->queueName);
        } catch (\Exception $e) {
            Logger::error("Error during add a job ($serializedJob): {$e->getMessage()}");
        }
        Logger::log("Job is successfully added: $serializedJob");
    }
}
