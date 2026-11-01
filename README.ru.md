# Localzet MQTT

[English documentation](README.md)

Клиент MQTT 3.1.1 и MQTT 5 для Localzet Server 7.

## Состояние и совместимость

Клиент использует API соединений и таймеров Localzet Server 7. Адаптеры MQTT 3.1.1 и MQTT 5 реализуют интерфейс протокола Server, а Composer PSR-4 соответствует namespace `localzet\\MQTT`.

Регрессионный набор проверяет частичные кадры, кодирование и декодирование ping-пакетов, а также выбор соединения по схемам `mqtt://` и `mqtts://`. Интеграционный набор запускает временный Mosquitto и проверяет логин/пароль, доставку и подтверждение QoS 1 для MQTT 3.1.1 и MQTT 5 по TCP и TLS, а также переподключение после перезапуска брокера и восстановление подписки.

## Зависимости

- `localzet/server`: `^7.0`

## Установка

Перед установкой проверьте, что выбранная версия Server удовлетворяет ограничению `^7.0`. Поведение с брокером пока экспериментальное; отдельно проверьте его на используемой конфигурации брокера и TLS.

## Проверки разработки

```sh
composer validate --strict
composer run test
composer run test:broker
```

`test:broker` требует Docker, поднимает временный Mosquitto и создаёт тестовые credentials и самоподписанный сертификат. Клиент отключает проверку сертификата только для этого временного теста, поэтому CI проверяет TLS handshake, но не проверку доверенной цепочки. Если порты 1883 или 8883 заняты, задайте `MQTT_TEST_PLAIN_PORT` и `MQTT_TEST_TLS_PORT`.

Установка, lint и автозагрузка не подтверждают сквозное поведение или готовность к эксплуатации.

## Автор и лицензия

Copyright © 2016-2026 [Localzet Group](https://www.localzet.com/). All rights reserved. AGPL-3.0-or-later; [LICENSE](LICENSE).

[Authors](.github/AUTHORS.md) · [Contributing](.github/CONTRIBUTING.md) · [Security](.github/SECURITY.md)
