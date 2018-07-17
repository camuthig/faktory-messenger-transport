# Faktory Messenger Transport

A [Symfony Messenger](https://symfony.com/doc/current/components/messenger.html) transport adapter for integrating with 
a [Faktory](http://contribsys.com/faktory/) server. 

**This library is in a proof-of-concept state and should not be considered production-ready without further testing and
finalization.**

## Usage

This package is not yet deployed to Packagist, so to use it you will need to include the GitHub repository as a new
composer repository in your `composer.json`.

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/camuthig/faktory-messenger-transport"
        }
    ]
}
```

You can then include it into your project like any other dependency.

```bash
composer require camuthig/faktory-messenger-transport:dev-master@dev
```

## Configuration

The expected transport DSN should be over TCP and look something like `MESSENGER_TRANSPORT_DSN=tcp://localhost:7419`.

Next ensure that the `FaktoryTransportFactory` and encoders/decoders are set in your DI container. This snippet could
be included in your `services.yaml` file, for example.

```yaml
App\Infrastructure\Messenger\FaktoryTransportFactory:
    tags: [messenger.transport_factory]

Symfony\Component\Messenger\Transport\Serialization\EncoderInterface: '@messenger.transport.serializer'
Symfony\Component\Messenger\Transport\Serialization\DecoderInterface: '@messenger.transport.serializer'
```
