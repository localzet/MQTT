# Localzet MQTT

[Русская документация](README.ru.md)

An unfinished MQTT client adaptation for Localzet Server.

## Status and compatibility

This source revision is not a usable Localzet MQTT package: source namespaces and runtime imports still target the original runtime while Composer declares localzet\MQTT. The MQTT 3/5 transport port, namespace compatibility, broker integration and parser limits must be completed. No installation example is presented as working.

This is a Server 4.x component; Server 7.x compatibility is not established.

## Dependencies

- `localzet/server`: `^4.1`

## Installation

The namespace/runtime port must be completed before usage.

## Development checks

```sh
composer validate --strict
# Autoload fails until the namespace/runtime port is complete.
```

Installation, lint and autoload checks do not establish end-to-end behavior or production readiness.

## Author and license

Ivan Zorin (`localzet`), <creator@localzet.com>, https://www.localzet.com.
Source: https://github.com/localzet/MQTT. AGPL-3.0-or-later; [LICENSE](LICENSE). Original copyright and third-party licenses remain applicable.

[Authors](.github/AUTHORS.md) · [Contributing](.github/CONTRIBUTING.md) · [Security](.github/SECURITY.md)
