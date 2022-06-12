<?php

declare(strict_types=1);

namespace App\Services;

use App\Logger;
use App\Queue\JobInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Queue
{
    private const QUEUE_NAME = 'default';

    public function __construct(private readonly AMQPStreamConnection $connection)
    {
        $this->connection->channel()->queue_declare(self::QUEUE_NAME, auto_delete: false);
    }

    public function add(JobInterface $job): void
    {
        $serializedJob = serialize($job);
        try {
            $msg = new AMQPMessage($serializedJob);
            $this->connection->channel()->basic_publish($msg, routing_key: self::QUEUE_NAME);
        } catch (\Exception $e) {
            Logger::error("Error during add a job ($serializedJob): {$e->getMessage()}");
        }
        Logger::log("Job is successfully added: $serializedJob");
    }

    public function work(): void
    {
        $channel = $this->connection->channel();
        $channel->basic_consume(
            self::QUEUE_NAME,
            callback: fn(JobInterface $job) => $job->execute()
        );
        while ($channel->is_open()) {
            $channel->wait();
        }
    }
}
