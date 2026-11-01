<?php

declare(strict_types=1);

/**
 * @package     Localzet MQTT
 * @link        https://github.com/localzet/MQTT
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright © 2016-2026 Localzet Group. All rights reserved.
 * @license     https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License v3.0
 */

use localzet\MQTT\Server\Protocols\Mqtt;
use localzet\MQTT\Server\Protocols\Mqtt5;
use localzet\MQTT\Client;
use localzet\Server\Connection\AsyncTcpConnection;
use localzet\Server\Connection\TcpConnection;

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $maps = [
            'localzet\\MQTT\\' => dirname(__DIR__) . '/src/',
            'localzet\\Server\\' => dirname(__DIR__, 2) . '/Server/src/',
            'localzet\\' => dirname(__DIR__, 2) . '/Server/',
        ];

        foreach ($maps as $prefix => $root) {
            if (str_starts_with($class, $prefix)) {
                $file = $root . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
                if (is_file($file)) {
                    require $file;
                }
                return;
            }
        }
    });
}

final class ProtocolTestConnection extends TcpConnection
{
    public function __construct()
    {
    }

    public function __destruct()
    {
    }
}

$connection = new ProtocolTestConnection();

foreach ([Mqtt::class, Mqtt5::class] as $protocol) {
    if ($protocol::input("\xC0", $connection) !== 0) {
        throw new RuntimeException("{$protocol} accepted an incomplete fixed header.");
    }

    if ($protocol::input("\xC0\x00\xD0\x00", $connection) !== 2) {
        throw new RuntimeException("{$protocol} did not stop at the first complete frame.");
    }

    if ($protocol::encode(['cmd' => 12], $connection) !== "\xC0\x00") {
        throw new RuntimeException("{$protocol} encoded PINGREQ incorrectly.");
    }

    if (($protocol::decode("\xC0\x00", $connection)['cmd'] ?? null) !== 12) {
        throw new RuntimeException("{$protocol} decoded PINGREQ incorrectly.");
    }
}

if (Mqtt5::input("\x30\x80", $connection) !== 0) {
    throw new RuntimeException('MQTT 5 accepted an incomplete remaining-length field.');
}

$connectionProperty = new ReflectionProperty(Client::class, '_connection');
$portProperty = new ReflectionProperty(AsyncTcpConnection::class, 'remotePort');
$mqttClient = new Client('mqtt://127.0.0.1');
$mqttConnection = $connectionProperty->getValue($mqttClient);
if ($mqttConnection->protocol !== Mqtt::class || $portProperty->getValue($mqttConnection) !== 1883) {
    throw new RuntimeException('MQTT address did not select the MQTT 3 protocol and default port.');
}

$mqtt5Client = new Client('mqtts://[::1]', ['protocol_level' => 5]);
$mqtt5Connection = $connectionProperty->getValue($mqtt5Client);
if ($mqtt5Connection->protocol !== Mqtt5::class || $mqtt5Connection->transport !== 'ssl' || $portProperty->getValue($mqtt5Connection) !== 8883) {
    throw new RuntimeException('Secure MQTT 5 address did not select TLS, the MQTT 5 protocol, and default port.');
}

echo "MQTT protocol regression checks passed.\n";
