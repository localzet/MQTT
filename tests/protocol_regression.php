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
use localzet\MQTT\Protocols\Mqtt as MqttCodec;
use localzet\MQTT\Client;
use localzet\Server;
use localzet\Server\Protocols\Mqtt as ServerMqtt;
use localzet\Server\Protocols\Mqtt5 as ServerMqtt5;
use localzet\Server\Connection\AsyncTcpConnection;
use localzet\Server\Connection\TcpConnection;

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $maps = [
            'localzet\\MQTT\\' => dirname(__DIR__) . '/src/',
            'localzet\\Server\\Protocols\\' => dirname(__DIR__) . '/src/Server/Protocols/',
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

    require dirname(__DIR__) . '/src/Server/Protocols/aliases.php';
}

foreach ([
    Mqtt::class => ServerMqtt::class,
    Mqtt5::class => ServerMqtt5::class,
] as $legacyClass => $serverClass) {
    if (!class_exists($legacyClass, false) || !is_a($legacyClass, $serverClass, true)) {
        throw new RuntimeException("Composer did not register the MQTT protocol alias {$legacyClass}.");
    }
}

foreach ([
    ['mqtt://127.0.0.1:0', ServerMqtt::class],
    ['mqtt5://127.0.0.1:0', ServerMqtt5::class],
] as [$address, $protocol]) {
    $listener = new Server($address);
    if ($listener->protocol !== $protocol) {
        throw new RuntimeException("Server did not resolve {$address} to {$protocol}.");
    }

    try {
        $listener->listen();
    } finally {
        $listener->unlisten();
    }
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

final class DebugCaptureConnection extends AsyncTcpConnection
{
    public array $sentPackages = [];

    public function __construct()
    {
    }

    public function send(mixed $data, bool $raw = false): ?bool
    {
        $this->sentPackages[] = $data;
        return true;
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

$debugClient = new Client('mqtt://127.0.0.1', [
    'debug' => true,
    'protocol_level' => 5,
    'username' => 'mqtt-debug-user-secret',
    'password' => 'mqtt-debug-password-secret',
]);
$debugConnection = new DebugCaptureConnection();
$connectionProperty->setValue($debugClient, $debugConnection);
$stateProperty = new ReflectionProperty(Client::class, '_state');
$receivedPublish = null;
$debugClient->onMessage = static function (string $topic, string $content) use (&$receivedPublish): void {
    $receivedPublish = [$topic, $content];
};
ob_start();
$debugClient->onConnectionConnect();
$stateProperty->setValue($debugClient, Client::STATE_ESTABLISHED);
$outgoingTopic = 'mqtt-debug-topic-secret';
$outgoingPayload = 'mqtt-debug-outgoing-payload-secret';
$outgoingProperty = 'mqtt-debug-outgoing-property-secret';
$subscriptionTopic = 'mqtt-debug-subscription-topic-secret';
$subscriptionProperty = 'mqtt-debug-subscription-property-secret';
$unsubscribeProperty = 'mqtt-debug-unsubscribe-property-secret';
$resubscribeTopic = 'mqtt-debug-resubscribe-topic-secret';
$debugClient->subscribe($subscriptionTopic, ['qos' => 1], null, [
    'user_properties' => [['name' => 'token', 'value' => $subscriptionProperty]],
]);
$debugClient->unsubscribe($subscriptionTopic, null, [
    'user_properties' => [['name' => 'token', 'value' => $unsubscribeProperty]],
]);
$debugClient->subscribe($resubscribeTopic, ['qos' => 1]);
$debugClient->onMqttReconnect();
$debugClient->publish($outgoingTopic, $outgoingPayload, [], null, [
    'user_properties' => [['name' => 'token', 'value' => $outgoingProperty]],
]);
$incomingTopic = 'mqtt-debug-incoming-topic-secret';
$incomingPayload = 'mqtt-debug-incoming-payload-secret';
$incomingProperty = 'mqtt-debug-incoming-property-secret';
$debugClient->onConnectionMessage($debugConnection, [
    'cmd' => MqttCodec::CMD_PUBLISH,
    'topic' => $incomingTopic,
    'content' => $incomingPayload,
    'qos' => 0,
    'properties' => ['user_properties' => [['name' => 'token', 'value' => $incomingProperty]]],
]);
$debugOutput = ob_get_clean();
$connectPackage = $debugConnection->sentPackages[0] ?? [];
$publishPackage = null;
foreach ($debugConnection->sentPackages as $sentPackage) {
    if (($sentPackage['cmd'] ?? null) === MqttCodec::CMD_PUBLISH) {
        $publishPackage = $sentPackage;
        break;
    }
}
if (($connectPackage['username'] ?? null) !== 'mqtt-debug-user-secret'
    || ($connectPackage['password'] ?? null) !== 'mqtt-debug-password-secret'
    || ($publishPackage['topic'] ?? null) !== $outgoingTopic
    || ($publishPackage['content'] ?? null) !== $outgoingPayload
    || $receivedPublish !== [$incomingTopic, $incomingPayload]
    || str_contains($debugOutput, $subscriptionTopic)
    || str_contains($debugOutput, $subscriptionProperty)
    || str_contains($debugOutput, $unsubscribeProperty)
    || str_contains($debugOutput, $resubscribeTopic)
    || str_contains($debugOutput, 'mqtt-debug-user-secret')
    || str_contains($debugOutput, 'mqtt-debug-password-secret')
    || str_contains($debugOutput, $outgoingTopic)
    || str_contains($debugOutput, $outgoingPayload)
    || str_contains($debugOutput, $outgoingProperty)
    || str_contains($debugOutput, $incomingTopic)
    || str_contains($debugOutput, $incomingPayload)
    || str_contains($debugOutput, $incomingProperty)
) {
    throw new RuntimeException('MQTT debug output must omit credentials and PUBLISH data while preserving delivery.');
}

echo "MQTT protocol regression checks passed.\n";
