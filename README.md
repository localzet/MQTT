# Localzet MQTT

[Русская документация](README.ru.md)

An MQTT 3.1.1 and MQTT 5 client for Localzet Server 7.

## Status and compatibility

The client uses the Localzet Server 7 connection and timer APIs. Its MQTT 3.1.1 and MQTT 5 protocol adapters implement Server's protocol interface. Composer exposes the listener adapters as `localzet\\Server\\Protocols\\Mqtt` and `localzet\\Server\\Protocols\\Mqtt5`, so Server 7 can resolve `mqtt://` and `mqtt5://` listener schemes. The `mqtts://` URL described below is a TLS client destination, not a Server listener scheme.

Protocol regression tests cover partial frames, ping encode/decode, and `mqtt://` / `mqtts://` address selection. The broker integration suite checks username/password authentication, QoS delivery and acknowledgements with MQTT 3.1.1 and MQTT 5 over TCP and TLS, broker restart and resubscription after reconnect, and rejection of untrusted certificates and mismatched peer names. It uses an ephemeral Mosquitto broker.

## Supported behavior

The client supports MQTT 3.1.1 and MQTT 5 over TCP and TLS, username/password authentication, QoS 0/1/2 publish and subscribe, acknowledgements and reconnect/resubscription. TLS certificate-chain and peer-name verification use the secure defaults from Localzet Server. For a private certificate authority, pass its PEM file in the `ssl` stream context; do not disable certificate or peer-name verification in production.

## Dependencies

- `php`: `>=8.1`
- `localzet/server`: `^7.0`

## Installation

Install the Server 7 release line:

```sh
composer require localzet/mqtt:^1.0
```

## Development checks

```sh
composer validate --strict
composer run test
composer run test:broker
composer audit --abandoned=report
```

`test:broker` requires Docker and uses a temporary Mosquitto broker. It generates disposable credentials, a self-signed server certificate, and an unrelated CA; tests cover successful verification with the trusted certificate and rejection of an untrusted CA and a mismatched peer name. Set `MQTT_TEST_PLAIN_PORT` and `MQTT_TEST_TLS_PORT` if local ports 1883 or 8883 are already in use.

The broker integration suite verifies the documented MQTT 3.1.1 and MQTT 5 flows against Mosquitto. It does not establish compatibility with every broker or deployment-specific certificate trust.

## Author and license

Copyright © 2016-2026 [Localzet Group](https://www.localzet.com/). All rights reserved. AGPL-3.0-or-later; [LICENSE](LICENSE).

[Authors](.github/AUTHORS.md) · [Contributing](.github/CONTRIBUTING.md) · [Security](.github/SECURITY.md)
