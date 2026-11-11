# Localzet MQTT

[English documentation](README.md)

Клиент MQTT 3.1.1 и MQTT 5 для Localzet Server 7.

## Состояние и совместимость

Клиент использует API соединений и таймеров Localzet Server 7. Адаптеры MQTT 3.1.1 и MQTT 5 реализуют интерфейс протокола Server, а Composer PSR-4 соответствует namespace `localzet\\MQTT`.

Регрессионный набор проверяет частичные кадры, кодирование и декодирование ping-пакетов, а также выбор соединения по схемам `mqtt://` и `mqtts://`. Интеграционный набор запускает временный Mosquitto и проверяет логин/пароль, доставку и подтверждение QoS 1 для MQTT 3.1.1 и MQTT 5 по TCP и TLS, а также переподключение после перезапуска брокера и восстановление подписки.

## Поддерживаемое поведение

Клиент поддерживает MQTT 3.1.1 и MQTT 5 по TCP и TLS, логин и пароль, публикацию и подписку QoS 0/1/2, подтверждения, переподключение и восстановление подписок. Интеграционные тесты проверяют QoS 1 и 2 на Mosquitto; проверьте поведение и настройку доверия TLS на используемом вами брокере.

## Зависимости

- `php`: `>=8.1`
- `localzet/server`: `^7.0`

## Установка

Установите линию релизов для Server 7:

```sh
composer require localzet/mqtt:^1.0
```

Интеграционные проверки подтверждают перечисленные сценарии на Mosquitto, но не совместимость со всеми брокерами. Проверьте работу и доверие к TLS-сертификату на целевой конфигурации.

## Проверки разработки

```sh
composer validate --strict
composer run test
composer run test:broker
composer audit --abandoned=report
```

`test:broker` требует Docker, поднимает временный Mosquitto и создаёт тестовые credentials и самоподписанный сертификат. Клиент отключает проверку сертификата только для этого временного теста, поэтому CI проверяет TLS handshake, но не проверку доверенной цепочки. Если порты 1883 или 8883 заняты, задайте `MQTT_TEST_PLAIN_PORT` и `MQTT_TEST_TLS_PORT`.

Интеграционный набор проверяет описанные сценарии MQTT 3.1.1 и MQTT 5 на Mosquitto. Он не подтверждает совместимость со всеми брокерами и доверие к сертификатам в конкретном окружении.

## Автор и лицензия

Copyright © 2016-2026 [Localzet Group](https://www.localzet.com/). All rights reserved. AGPL-3.0-or-later; [LICENSE](LICENSE).

[Authors](.github/AUTHORS.md) · [Contributing](.github/CONTRIBUTING.md) · [Security](.github/SECURITY.md)
