<?php

declare(strict_types=1);

namespace Camuthig\FaktoryMessenger;

use Camuthig\Faktory\Client;
use Camuthig\Faktory\Consumer;
use Camuthig\Faktory\Producer;
use Symfony\Component\Messenger\Transport\Serialization\DecoderInterface;
use Symfony\Component\Messenger\Transport\Serialization\EncoderInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class FaktoryTransportFactory implements TransportFactoryInterface
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    public function __construct(EncoderInterface $encoder, DecoderInterface $decoder)
    {
        $this->encoder = $encoder;
        $this->decoder = $decoder;
    }

    public function createTransport(string $dsn, array $options): TransportInterface
    {
        $producer = new Producer(new Client($this->getHostFromDsn($dsn), $this->getPortFromDsn($dsn)));
        $consumer = new Consumer(new Client($this->getHostFromDsn($dsn), $this->getPortFromDsn($dsn), [
            'wid' => uniqid()
        ]));

        return new FaktoryTransport($this->encoder, $this->decoder, $producer, $consumer, $options['queues'] ?? []);
    }

    public function supports(string $dsn, array $options): bool
    {
        return strpos($dsn, 'tcp://') === 0 && $this->getPortFromDsn($dsn);
    }

    private function getPortFromDsn(string $dsn): int
    {
        $parts = explode(':', $dsn);
        return (int) end($parts);
    }

    private function getHostFromDsn(string $dsn): string
    {
        $parts = explode(':', $dsn);
        array_pop($parts);
        return substr(implode(':', $parts), strlen('tcp://'));
    }
}
