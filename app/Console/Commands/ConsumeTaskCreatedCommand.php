<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

final class ConsumeTaskCreatedCommand extends Command
{
    protected $signature = 'rabbitmq:consume-task-created {--once : Consume only one message}';

    protected $description = 'Consumes task.created events and simulates an email notification.';

    private static ?AMQPStreamConnection $connection = null;
    private static ?\PhpAmqpLib\Channel\AMQPChannel $channel = null;

    /**
     * Consome eventos task.created do RabbitMQ.
     *
     * @author André Narcizo
     */
    public function handle(): int
    {
        $channel = $this->getChannel();

        $channel->basic_consume('task-created-email', '', false, false, false, false, function ($message): void {
            logger()->info('Simulated task created email', [
                'payload' => json_decode($message->body, true),
            ]);
            $message->ack();

            if ($this->option('once')) {
                $message->getChannel()->stopConsume();
            }
        });

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        return self::SUCCESS;
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
            self::$channel->exchange_declare('tasks', 'topic', false, true, false);
            self::$channel->queue_declare('task-created-email', false, true, false, false);
            self::$channel->queue_bind('task-created-email', 'tasks', 'task.created');
        }
        return self::$channel;
    }
}
