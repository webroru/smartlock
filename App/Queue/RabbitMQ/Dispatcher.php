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
    private const EXCHANGE_DECLARE = 'delayed_exchange';
    public function __construct(
        private readonly AMQPStreamConnection $connection,
        private readonly string $queueName
    ) {
        $this->connection->channel()->queue_declare(
            $this->queueName,
            durable: true,
            auto_delete: false,
        );
        $this->connection->channel()->exchange_declare(
            self::EXCHANGE_DECLARE,
            'x-delayed-message',
            durable: true,
            auto_delete: false,
            arguments: new AMQPTable(['x-delayed-type' => AMQPExchangeType::FANOUT]),
        );
        $this->connection->channel()->queue_bind($this->queueName, self::EXCHANGE_DECLARE, $this->queueName);
    }

    public function add(JobInterface $job, int $delay = 0): void
    {
        $serializedJob = serialize($job);
        try {
            $msg = new AMQPMessage($serializedJob);
            $msg->set('delivery_mode', AMQPMessage::DELIVERY_MODE_PERSISTENT);
            if ($delay) {
                $headers = new AMQPTable(['x-delay' => $delay * 1000]);
                $msg->set('application_headers', $headers);
            }
            $this->connection->channel()->basic_publish($msg, self::EXCHANGE_DECLARE, $this->queueName);
        } catch (\Exception $e) {
            Logger::error("Error during add a job ($serializedJob): {$e->getMessage()}");
        }
    }
}
