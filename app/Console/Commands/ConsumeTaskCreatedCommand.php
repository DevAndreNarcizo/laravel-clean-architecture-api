<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

final class ConsumeTaskCreatedCommand extends Command
{
    protected $signature = 'rabbitmq:consume-task-created {--once : Consume only one message}';

    protected $description = 'Consumes task.created events and simulates an email notification.';

    /**
     * Consome eventos task.created do RabbitMQ.
     *
     * @author André Narcizo
     */
    public function handle(): int
    {
        $connection = new AMQPStreamConnection(
            (string) config('services.rabbitmq.host'),
            (int) config('services.rabbitmq.port'),
            (string) config('services.rabbitmq.user'),
            (string) config('services.rabbitmq.password'),
        );
        $channel = $connection->channel();
        $channel->exchange_declare('tasks', 'topic', false, true, false);
        $channel->queue_declare('task-created-email', false, true, false, false);
        $channel->queue_bind('task-created-email', 'tasks', 'task.created');

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

        $channel->close();
        $connection->close();

        return self::SUCCESS;
    }
}
