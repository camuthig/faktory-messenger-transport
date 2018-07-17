<?php

declare(strict_types=1);

namespace Camuthig\FaktoryMessenger;

use Camuthig\Faktory\ProducerInterface;
use Camuthig\Faktory\WorkUnit;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\SenderInterface;
use Symfony\Component\Messenger\Transport\Serialization\EncoderInterface;

class FaktorySender implements SenderInterface
{
    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    public function __construct(ProducerInterface $producer, EncoderInterface $encoder)
    {
        $this->producer = $producer;
        $this->encoder = $encoder;
    }

    /**
     * Sends the given envelope.
     *
     * @param Envelope $envelope
     */
    public function send(Envelope $envelope)
    {
        $encodedEnvelope = $this->encoder->encode($envelope);

        $this->producer->push(new WorkUnit(uniqid(), get_class($envelope->getMessage()), [$encodedEnvelope]));
    }
}
