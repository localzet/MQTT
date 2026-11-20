#!/usr/bin/env bash
# Copyright © 2016-2026 Localzet Group. All rights reserved.
# SPDX-License-Identifier: AGPL-3.0-or-later
set -euo pipefail

image='eclipse-mosquitto:2.0.21'
plain_port="${MQTT_TEST_PLAIN_PORT:-1883}"
tls_port="${MQTT_TEST_TLS_PORT:-8883}"
workdir="$(mktemp -d)"
container="localzet-mqtt-test-${$}"
cleanup() {
    docker rm -f "$container" >/dev/null 2>&1 || true
    rm -rf "$workdir"
}
trap cleanup EXIT

chmod 777 "$workdir"
docker run --rm --user 0:0 --entrypoint /bin/sh -v "$workdir:/mosquitto/config" "$image" \
    -c 'mosquitto_passwd -b -c /mosquitto/config/passwd integration-user integration-password && chmod 644 /mosquitto/config/passwd'
openssl req -x509 -newkey rsa:2048 -nodes \
    -keyout "$workdir/key.pem" -out "$workdir/cert.pem" -days 1 \
    -subj '/CN=localhost' -addext 'subjectAltName=IP:127.0.0.1,DNS:localhost' >/dev/null 2>&1
openssl req -x509 -newkey rsa:2048 -nodes \
    -keyout "$workdir/untrusted-key.pem" -out "$workdir/untrusted-ca.pem" -days 1 \
    -subj '/CN=Localzet MQTT integration untrusted CA' >/dev/null 2>&1
chmod 644 "$workdir/key.pem" "$workdir/cert.pem" "$workdir/untrusted-ca.pem"
cp "$(dirname "$0")/mosquitto.conf" "$workdir/mosquitto.conf"
docker run -d --name "$container" \
    -p "127.0.0.1:${plain_port}:1883" -p "127.0.0.1:${tls_port}:8883" \
    -v "$workdir:/mosquitto/config:ro" "$image" \
    mosquitto -c /mosquitto/config/mosquitto.conf >/dev/null

ready=0
for _ in $(seq 1 30); do
    # shellcheck disable=SC2016
    if php -r '$s = @fsockopen("127.0.0.1", (int) $argv[1], $errno, $error, 0.2); exit($s ? 0 : 1);' "$plain_port"; then
        ready=1
        break
    fi
    sleep 0.2
done
if [[ "$ready" != 1 ]]; then
    docker logs "$container"
    exit 1
fi

run_case() {
    MQTT_TEST_URL="$1" MQTT_TEST_PROTOCOL_LEVEL="$2" MQTT_TEST_QOS="$3" \
        MQTT_TEST_TLS_CAFILE="${4:-}" MQTT_TEST_TLS_PEER_NAME="${5:-}" \
        MQTT_TEST_EXPECT_TLS_FAILURE="${6:-0}" \
        MQTT_TEST_USERNAME=integration-user MQTT_TEST_PASSWORD=integration-password \
        php -d disable_functions=pcntl_fork "$(dirname "$0")/broker_integration.php" start
}

assert_tls_rejection() {
    # shellcheck disable=SC2016
    TLS_TEST_PORT="$tls_port" TLS_TEST_CA_FILE="$1" TLS_TEST_PEER_NAME="$2" php -r '
        $warnings = [];
        set_error_handler(static function (int $severity, string $message) use (&$warnings): bool {
            $warnings[] = $message;
            return true;
        });
        $context = stream_context_create(["ssl" => [
            "verify_peer" => true,
            "verify_peer_name" => true,
            "allow_self_signed" => false,
            "cafile" => getenv("TLS_TEST_CA_FILE"),
            "peer_name" => getenv("TLS_TEST_PEER_NAME"),
        ]]);
        $socket = stream_socket_client(
            "tls://127.0.0.1:" . getenv("TLS_TEST_PORT"),
            $errno,
            $errorMessage,
            3,
            STREAM_CLIENT_CONNECT,
            $context
        );
        restore_error_handler();

        if (is_resource($socket)) {
            fclose($socket);
            fwrite(STDERR, "TLS preflight unexpectedly accepted the test certificate.\n");
            exit(1);
        }

        $details = implode("\n", $warnings) . "\n" . $errorMessage;
        if (!preg_match("/certificate verify failed|unable to get local issuer certificate|self-signed certificate|did not match|name mismatch/i", $details)) {
            fwrite(STDERR, "TLS preflight failed for a non-verification reason: {$details}\n");
            exit(1);
        }
    '
}

for protocol_level in 4 5; do
    for qos in 1 2; do
        run_case "mqtt://127.0.0.1:${plain_port}" "$protocol_level" "$qos"
        run_case "mqtts://127.0.0.1:${tls_port}" "$protocol_level" "$qos" "$workdir/cert.pem"
    done
done

assert_tls_rejection "$workdir/untrusted-ca.pem" '127.0.0.1'
run_case "mqtts://127.0.0.1:${tls_port}" 4 1 "$workdir/untrusted-ca.pem" '' 1
assert_tls_rejection "$workdir/cert.pem" 'wrong.localhost'
run_case "mqtts://127.0.0.1:${tls_port}" 4 1 "$workdir/cert.pem" 'wrong.localhost' 1

run_reconnect_case() {
    marker="$workdir/reconnect-ready"
    log="$workdir/reconnect.log"
    rm -f "$marker" "$log"
    MQTT_TEST_URL="mqtt://127.0.0.1:${plain_port}" MQTT_TEST_PROTOCOL_LEVEL="$1" \
        MQTT_TEST_USERNAME=integration-user MQTT_TEST_PASSWORD=integration-password \
        MQTT_TEST_RECONNECT=1 MQTT_TEST_RESTART_FILE="$marker" \
        php -d disable_functions=pcntl_fork "$(dirname "$0")/broker_integration.php" start >"$log" 2>&1 &
    test_pid=$!

    ready=0
    for _ in $(seq 1 100); do
        if [[ -f "$marker" ]]; then
            ready=1
            break
        fi
        if ! kill -0 "$test_pid" 2>/dev/null; then
            wait "$test_pid" || true
            cat "$log"
            return 1
        fi
        sleep 0.1
    done
    if [[ "$ready" != 1 ]]; then
        kill "$test_pid" 2>/dev/null || true
        wait "$test_pid" || true
        cat "$log"
        echo 'Timed out waiting for the initial MQTT subscription.' >&2
        return 1
    fi

    docker stop "$container" >/dev/null
    sleep 0.5
    docker start "$container" >/dev/null
    if ! wait "$test_pid"; then
        cat "$log"
        return 1
    fi
    cat "$log"
}

run_reconnect_case 4
run_reconnect_case 5
