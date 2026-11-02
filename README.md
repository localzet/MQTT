# Localzet MQTT

[Русская документация](README.ru.md)

An MQTT 3.1.1 and MQTT 5 client for Localzet Server 7.

## Status and compatibility

The client uses the Localzet Server 7 connection and timer APIs. Its MQTT 3.1.1 and MQTT 5 protocol adapters implement Server's protocol interface, and Composer autoloading matches the `localzet\\MQTT` namespace.

Protocol regression tests cover partial frames, ping encode/decode, and `mqtt://` / `mqtts://` address selection. The broker integration suite checks username/password authentication, QoS 1 delivery and acknowledgements with MQTT 3.1.1 and MQTT 5 over TCP and TLS, plus broker restart and resubscription after reconnect. It uses an ephemeral Mosquitto broker.

## Dependencies

- `localzet/server`: `^7.0`

## Installation

The package is not published on Packagist yet. Add the VCS repository to the application's `composer.json` and install branch `dev-main`:

```sh
composer config repositories.localzet-mqtt vcs https://github.com/localzet/MQTT
composer require localzet/mqtt:dev-main
```

Broker-level behavior remains experimental; validate it against the broker and TLS configuration used by your application.

## Development checks

```sh
composer validate --strict
composer run test
composer run test:broker
```

`test:broker` requires Docker and uses a temporary Mosquitto broker. It generates disposable credentials and a self-signed certificate; set `MQTT_TEST_PLAIN_PORT` and `MQTT_TEST_TLS_PORT` if local ports 1883 or 8883 are already in use. Test clients disable certificate verification for the ephemeral self-signed certificate, so CI verifies TLS negotiation, not certificate trust configuration.

Installation, lint and autoload checks do not establish end-to-end behavior or production readiness.

## Author and license

Copyright © 2016-2026 [Localzet Group](https://www.localzet.com/). All rights reserved. AGPL-3.0-or-later; [LICENSE](LICENSE).

[Authors](.github/AUTHORS.md) · [Contributing](.github/CONTRIBUTING.md) · [Security](.github/SECURITY.md)
