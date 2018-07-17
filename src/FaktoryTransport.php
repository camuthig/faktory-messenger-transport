<?php

declare(strict_types=1);

namespace Camuthig\FaktoryMessenger;

use Camuthig\Faktory\ConsumerInterface;
use Camuthig\Faktory\ProducerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\DecoderInterface;
use Symfony\Component\Messenger\Transport\Serialization\EncoderInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class FaktoryTransport implements TransportInterface
{
    /**
     * @var FaktoryReceiver
     */
    private $receiver;

    /**
     * @var FaktorySender
     */
    private $sender;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @var ConsumerInterface
     */
    private $consumer;

    /**
     * @var array
     */
    private $queues;

    public function __construct(
        EncoderInterface $encoder,
        DecoderInterface $decoder,
        ProducerInterface $producer,
        ConsumerInterface $consumer,
        array $queues = []
    ) {
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->producer = $producer;
        $this->consumer = $consumer;
        $this->queues = $queues;
    }

    /**
     * Receive some messages to the given handler.
     *
     * The handler will have, as argument, the received {@link \Symfony\Component\Messenger\Envelope} containing
     * the message. Note that this envelope can be `null` if the timeout to receive something has expired.
     */
    public function receive(callable $handler): void
    {
        $this->getReceiver()->receive($handler);
    }

    /**
     * Stop receiving some messages.
     */
    public function stop(): void
    {
        $this->getReceiver()->stop();
    }

    /**
     * Sends the given envelope.
     *
     * @param Envelope $envelope
     */
    public function send(Envelope $envelope): void
    {
        $this->getSender()->send($envelope);
    }

    private function getReceiver(): FaktoryReceiver
    {
        if ($this->receiver === null) {
            $this->receiver = new FaktoryReceiver($this->consumer, $this->decoder, $this->queues);
        }

        return $this->receiver;
    }

    private function getSender(): FaktorySender
    {
        if ($this->sender === null) {
            $this->sender = new FaktorySender($this->producer, $this->encoder);
        }

        return $this->sender;
    }
}
