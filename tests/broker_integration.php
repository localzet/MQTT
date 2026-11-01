<?php

declare(strict_types=1);

/**
 * @package     Localzet MQTT
 * @link        https://github.com/localzet/MQTT
 * @copyright   Copyright © 2016-2026 Localzet Group. All rights reserved.
 * @license     https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License v3.0
 */

use localzet\MQTT\Client;
use localzet\Server;
use localzet\Timer;

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
} else {
    require dirname(__DIR__, 2) . '/Server/vendor/autoload.php';
    spl_autoload_register(static function (string $class): void {
        $prefix = 'localzet\\MQTT\\';
        if (str_starts_with($class, $prefix)) {
            $file = dirname(__DIR__) . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) require $file;
        }
    });
}

$level = (int) (getenv('MQTT_TEST_PROTOCOL_LEVEL') ?: '4');
$GLOBALS['mqttTestLevel'] = $level;
$GLOBALS['mqttTestPassed'] = false;
if (!in_array($level, [4, 5], true)) {
    throw new InvalidArgumentException('MQTT_TEST_PROTOCOL_LEVEL must be 4 or 5.');
}
$address = getenv('MQTT_TEST_URL') ?: 'mqtt://127.0.0.1:1883';
$reconnectScenario = getenv('MQTT_TEST_RECONNECT') === '1';
$restartFile = getenv('MQTT_TEST_RESTART_FILE') ?: '';
if ($reconnectScenario && $restartFile === '') {
    throw new InvalidArgumentException('MQTT_TEST_RESTART_FILE is required for reconnect tests.');
}
$options = [
    'protocol_level' => $level,
    'username' => getenv('MQTT_TEST_USERNAME') ?: 'integration-user',
    'password' => getenv('MQTT_TEST_PASSWORD') ?: 'integration-password',
    'connect_timeout' => 5,
    'keepalive' => 0,
    'reconnect_period' => $reconnectScenario ? 1 : 0,
];
if (str_starts_with($address, 'mqtts://')) {
    $options['ssl'] = ['verify_peer' => false, 'verify_peer_name' => false];
}

Server::$pidFile = sys_get_temp_dir() . '/localzet-mqtt-test-' . getmypid() . '.pid';
Server::$logFile = sys_get_temp_dir() . '/localzet-mqtt-test-' . getmypid() . '.log';
$controlAddress = getenv('MQTT_TEST_CONTROL_ADDRESS');
if (!$controlAddress) {
    $probe = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
    if ($probe === false) throw new RuntimeException('Could not allocate the local event-loop listener.');
    $controlAddress = 'tcp://' . stream_socket_get_name($probe, false);
    fclose($probe);
}
$worker = new Server($controlAddress);
$worker->count = 1;
$worker->name = 'mqtt-integration';
$worker->onServerStart = static function () use ($address, $options, $level, $reconnectScenario, $restartFile): void {
    $topic = 'localzet/integration/' . bin2hex(random_bytes(8));
    $payload = 'mqtt-v' . $level . '-' . bin2hex(random_bytes(8));
    $finished = false;
    $messageReceived = false;
    $publishAcknowledged = false;
    $subscriber = new Client($address, $options + ['client_id' => 'subscriber-' . getmypid()]);
    $publisher = new Client($address, $options + ['client_id' => 'publisher-' . getmypid()]);

    $finish = static function (int $code, string $message = '') use (&$finished, $subscriber, $publisher): void {
        if ($finished) return;
        $finished = true;
        $GLOBALS['mqttTestPassed'] = $code === 0;
        fwrite(STDERR, $code === 0 ? "MQTT broker integration passed (protocol level {$GLOBALS['mqttTestLevel']}).\n" : "MQTT broker integration failed: {$message}\n");
        $subscriber->close();
        $publisher->close();
        Server::stopAll($code, $message);
    };

    $subscriber->onError = static function (Throwable $error) use ($finish): void {
        $finish(1, 'Subscriber failed: ' . $error->getMessage());
    };
    $publisher->onError = static function (Throwable $error) use ($finish): void {
        $finish(1, 'Publisher failed: ' . $error->getMessage());
    };
    $publish = static function () use ($publisher, $topic, $payload, &$messageReceived, &$publishAcknowledged, $finish): void {
        $publisher->onConnect = static function (Client $client) use ($topic, $payload, &$messageReceived, &$publishAcknowledged, $finish): void {
            $client->publish($topic, $payload, ['qos' => 1], static function (?Throwable $error) use (&$messageReceived, &$publishAcknowledged, $finish): void {
                if ($error !== null) {
                    $finish(1, 'QoS 1 publish was not acknowledged: ' . $error->getMessage());
                    return;
                }
                $publishAcknowledged = true;
                if ($messageReceived) $finish(0);
            });
        };
        $publisher->connect();
    };
    $subscriber->onMessage = static function (string $receivedTopic, string $receivedPayload) use ($topic, $payload, &$messageReceived, &$publishAcknowledged, $finish): void {
        if ($receivedTopic !== $topic || $receivedPayload !== $payload) {
            $finish(1, 'Broker returned a different topic or payload.');
            return;
        }
        Timer::add(0.05, static function () use (&$messageReceived, &$publishAcknowledged, $finish): void {
            $messageReceived = true;
            if ($publishAcknowledged) $finish(0);
        }, [], false);
    };
    $defaultReconnect = $subscriber->onReconnect;
    $subscriber->onReconnect = static function (Client $client) use ($defaultReconnect, $reconnectScenario, $publish): void {
        if (is_callable($defaultReconnect)) $defaultReconnect($client);
        if ($reconnectScenario) Timer::add(0.25, $publish, [], false);
    };
    $subscriber->onConnect = static function (Client $client) use ($topic, $reconnectScenario, $restartFile, $publish, $finish): void {
        $client->subscribe($topic, ['qos' => 1], static function (?Throwable $error) use ($reconnectScenario, $restartFile, $publish, $finish): void {
            if ($error !== null) {
                $finish(1, 'Subscribe failed: ' . $error->getMessage());
                return;
            }
            if ($reconnectScenario) {
                file_put_contents($restartFile, 'subscribed');
                return;
            }
            $publish();
        });
    };

    Timer::add(25, static fn () => $finish(1, 'Timed out waiting for authenticated MQTT QoS 1 delivery and acknowledgement.'), [], false);
    $subscriber->connect();
};

Server::runAll();
if (!$GLOBALS['mqttTestPassed']) {
    exit(1);
}
