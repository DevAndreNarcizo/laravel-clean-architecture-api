<?php

declare(strict_types=1);

namespace Src\Infrastructure\Messaging;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Src\Domain\Project\Task;

final class TaskCreatedPublisher
{
    private static ?AMQPStreamConnection $connection = null;
    private static ?AMQPChannel $channel = null;

    /**
     * Publica evento task.created no RabbitMQ quando habilitado.
     *
     * @author André Narcizo
     */
    public function publish(Task $task): void
    {
        if (! (bool) config('services.rabbitmq.enabled', false)) {
            return;
        }

        $channel = $this->getChannel();
        $channel->exchange_declare('tasks', 'topic', false, true, false);

        $message = new AMQPMessage(json_encode([
            'event' => 'task.created',
            'task_id' => $task->id,
            'project_id' => $task->projectId,
            'title' => $task->title,
            'occurred_at' => now()->toISOString(),
        ], JSON_THROW_ON_ERROR), [
            'content_type' => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);

        $channel->basic_publish($message, 'tasks', 'task.created');
    }

    public function __destruct()
    {
        if (self::$channel !== null) {
            self::$channel->close();
            self::$channel = null;
        }
        if (self::$connection !== null && self::$connection->isConnected()) {
            self::$connection->close();
            self::$connection = null;
        }
    }

    private function getChannel(): \PhpAmqpLib\Channel\AMQPChannel
    {
        if (self::$channel === null || self::$connection === null || !self::$connection->isConnected()) {
            if (self::$connection !== null && self::$connection->isConnected()) {
                self::$connection->close();
            }
            self::$connection = new AMQPStreamConnection(
                (string) config('services.rabbitmq.host'),
                (int) config('services.rabbitmq.port'),
                (string) config('services.rabbitmq.user'),
                (string) config('services.rabbitmq.password'),
            );
            self::$channel = self::$connection->channel();
        }
        return self::$channel;
    }
}
