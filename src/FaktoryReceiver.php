<?php

declare(strict_types=1);

namespace Camuthig\FaktoryMessenger;

use Camuthig\Faktory\ConsumerInterface;
use Symfony\Component\Messenger\Transport\ReceiverInterface;
use Symfony\Component\Messenger\Transport\Serialization\DecoderInterface;

class FaktoryReceiver implements ReceiverInterface
{
    /**
     * @var ConsumerInterface
     */
    private $consumer;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var string[]
     */
    private $queues;

    /**
     * @var bool
     */
    private $shouldStop = false;

    public function __construct(ConsumerInterface $consumer, DecoderInterface $decoder, array $queues)
    {
        $this->consumer = $consumer;
        $this->decoder = $decoder;
        $this->queues  = $queues;
    }

    /**
     * Receive some messages to the given handler.
     *
     * The handler will have, as argument, the received {@link \Symfony\Component\Messenger\Envelope} containing
     * the message. Note that this envelope can be `null` if the timeout to receive something has expired.
     */
    public function receive(callable $handler): void
    {
        $status = null;

        while (!$this->shouldStop) {
            if ($status === ConsumerInterface::TERMINATE) {
                $this->stop();
            } elseif ($status === ConsumerInterface::QUIET) {
                continue;
            } else {
                // TODO: Would be nice to be able to configure these queues on the command line
                $job = $this->consumer->fetch(...$this->queues);

                if ($job === null) {
                    $handler(null);

                    usleep(200000);
                    if (\function_exists('pcntl_signal_dispatch')) {
                        pcntl_signal_dispatch();
                    }

                    $status = $this->consumer->beat();

                    continue;
                }

                try {
                    $envelope = $this->decoder->decode($job->getArgs()[0]);

                    $handler($envelope);

                    $this->consumer->ack($job);
                } catch (\Throwable $t) {
                    $this->consumer->fail($job, get_class($t), $t->getMessage(), $t->getTraceAsString());
                } finally {
                    if (\function_exists('pcntl_signal_dispatch')) {
                        pcntl_signal_dispatch();
                    }
                }
            }

            $status = $this->consumer->beat();
        }

        $this->consumer->end();
    }

    /**
     * Stop receiving some messages.
     */
    public function stop(): void
    {
        $this->shouldStop = true;
    }
}
