# Localzet MQTT

[Русская документация](README.ru.md)

An MQTT 3.1.1 and MQTT 5 client for Localzet Server 7.

## Status and compatibility

The client uses the Localzet Server 7 connection and timer APIs. Its MQTT 3.1.1 and MQTT 5 protocol adapters implement Server's protocol interface, and Composer autoloading matches the `localzet\\MQTT` namespace.

Protocol regression tests cover partial frames, ping encode/decode, and `mqtt://` / `mqtts://` address selection. The broker integration suite checks username/password authentication, QoS 1 delivery and acknowledgements with MQTT 3.1.1 and MQTT 5 over TCP and TLS, plus broker restart and resubscription after reconnect. It uses an ephemeral Mosquitto broker.

## Supported behavior

The client supports MQTT 3.1.1 and MQTT 5 over TCP and TLS, username/password authentication, QoS 0/1/2 publish and subscribe, acknowledgements and reconnect/resubscription. Integration coverage exercises QoS 1 and 2 against Mosquitto; validate behavior and TLS trust configuration with the broker deployment you operate.

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

`test:broker` requires Docker and uses a temporary Mosquitto broker. It generates disposable credentials and a self-signed certificate; set `MQTT_TEST_PLAIN_PORT` and `MQTT_TEST_TLS_PORT` if local ports 1883 or 8883 are already in use. Test clients disable certificate verification for the ephemeral self-signed certificate, so CI verifies TLS negotiation, not certificate trust configuration.

The broker integration suite verifies the documented MQTT 3.1.1 and MQTT 5 flows against Mosquitto. It does not establish compatibility with every broker or deployment-specific certificate trust.

## Author and license

Copyright © 2016-2026 [Localzet Group](https://www.localzet.com/). All rights reserved. AGPL-3.0-or-later; [LICENSE](LICENSE).

[Authors](.github/AUTHORS.md) · [Contributing](.github/CONTRIBUTING.md) · [Security](.github/SECURITY.md)
