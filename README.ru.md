# Localzet MQTT

[English documentation](README.md)

Незавершённая адаптация MQTT-клиента под Localzet Server.

## Состояние и совместимость

Эта версия не является работоспособным пакетом Localzet MQTT: namespace и импорты кода относятся к исходному runtime, а Composer объявляет localzet\MQTT. Нужны адаптация транспорта MQTT 3/5, согласование namespace, интеграционные проверки с брокером и ограничения парсера. Рабочий пример установки пока не заявляется.

Это компонент для Server 4.x; совместимость с Server 7.x не установлена.

## Зависимости

- `localzet/server`: `^4.1`

## Установка

До использования нужно завершить адаптацию namespace и runtime.

## Проверки разработки

```sh
composer validate --strict
# Autoload currently fails; see the status section.
```

Установка, lint и автозагрузка не подтверждают сквозное поведение или готовность к эксплуатации.

## Автор и лицензия

Ivan Zorin (`localzet`), <creator@localzet.com>, https://www.localzet.com.
Source: https://github.com/localzet/MQTT. AGPL-3.0-or-later; [LICENSE](LICENSE). Сохраняются исходные уведомления авторов и лицензии сторонних компонентов.

[Authors](.github/AUTHORS.md) · [Contributing](.github/CONTRIBUTING.md) · [Security](.github/SECURITY.md)
