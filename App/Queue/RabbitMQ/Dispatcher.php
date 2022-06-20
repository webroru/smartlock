<?php

declare(strict_types=1);

namespace App\Queue\RabbitMQ;

use App\Logger;
use App\Queue\DispatcherInterface;
use App\Queue\Job\JobInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class Dispatcher implements DispatcherInterface
{
    public function __construct(
        private readonly AMQPStreamConnection $connection,
        private readonly string $queueName
    ) {
        $this->connection->channel()->queue_declare($this->queueName, auto_delete: false);
        $this->connection->channel()->exchange_declare(
            'delayed_exchange',
            'x-delayed-message',
            arguments: new AMQPTable(['x-delayed-type' => AMQPExchangeType::FANOUT])
        );
        $this->connection->channel()->queue_bind($this->queueName, 'delayed_exchange');
    }

    public function add(JobInterface $job, int $delay = 0): void
    {
        $serializedJob = serialize($job);
        try {
            $msg = new AMQPMessage($serializedJob);
            if ($delay) {
                $headers = new AMQPTable(['x-delay' => $delay * 1000]);
                $msg->set('delivery_mode', 2);
                $msg->set('application_headers', $headers);
            }
            $this->connection->channel()->basic_publish($msg, routing_key: $this->queueName);
        } catch (\Exception $e) {
            Logger::error("Error during add a job ($serializedJob): {$e->getMessage()}");
        }
        Logger::log("Job is successfully added: $serializedJob");
    }
}
