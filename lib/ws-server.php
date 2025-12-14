<?php
// WebSocket server for quiz result updates.
// Run from CLI: php lib/ws-server.php

set_time_limit(0);

$host = '0.0.0.0';
$port = 3001;

$server = @stream_socket_server("tcp://{$host}:{$port}", $errno, $errstr);
if (!$server) {
    fwrite(STDERR, "Не удалось запустить сервер: {$errstr} ({$errno})\n");
    exit(1);
}

stream_set_blocking($server, false);

$clients = [];
$handshakes = [];

echo "WebSocket сервер запущен на {$host}:{$port}\n";

descriptor_loop:
while (true) {
    $read = [$server];
    foreach ($clients as $client) {
        $read[] = $client;
    }

    $write = null;
    $except = null;

    if (stream_select($read, $write, $except, 1) === false) {
        continue;
    }

    if (in_array($server, $read, true)) {
        $connection = @stream_socket_accept($server, 0);
        if ($connection) {
            stream_set_blocking($connection, false);
            $id = (int) $connection;
            $clients[$id] = $connection;
            $handshakes[$id] = false;
        }
        unset($read[array_search($server, $read, true)]);
    }

    foreach ($read as $client) {
        $id = (int) $client;
        $data = @fread($client, 2048);

        if ($data === '' || $data === false) {
            if (feof($client)) {
                cleanupClient($id, $clients, $handshakes);
            }
            continue;
        }

        if (!$handshakes[$id]) {
            if (!performHandshake($client, $data)) {
                cleanupClient($id, $clients, $handshakes);
            } else {
                $handshakes[$id] = true;
            }
            continue;
        }

        $payload = decodeFrame($data);
        if ($payload === null) {
            continue;
        }

        $message = json_decode($payload, true);
        if (!is_array($message) || ($message['type'] ?? '') !== 'quiz_result') {
            // Ignore any non-quiz payloads to keep the channel clean.
            continue;
        }

        $outgoing = encodeFrame(json_encode([
            'type' => 'quiz_result',
            'quizId' => $message['quizId'] ?? null,
            'participantId' => $message['participantId'] ?? null,
            'nickname' => $message['nickname'] ?? null,
            'realname' => $message['realname'] ?? null,
            'payload' => $message['payload'] ?? null,
        ], JSON_UNESCAPED_UNICODE));

        foreach ($clients as $clientId => $connection) {
            if (!empty($handshakes[$clientId])) {
                @fwrite($connection, $outgoing);
            }
        }
    }
}

function cleanupClient(int $id, array &$clients, array &$handshakes): void
{
    if (isset($clients[$id])) {
        @fclose($clients[$id]);
        unset($clients[$id]);
    }
    unset($handshakes[$id]);
}

function performHandshake($client, string $headers): bool
{
    if (!preg_match("/Sec-WebSocket-Key: (.*)\r\n/i", $headers, $match)) {
        return false;
    }

    $key = trim($match[1]);
    $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));

    $response = "HTTP/1.1 101 Switching Protocols\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Accept: {$acceptKey}\r\n\r\n";

    return (bool) @fwrite($client, $response);
}

function decodeFrame(string $data): ?string
{
    $length = strlen($data);
    if ($length < 2) {
        return null;
    }

    $firstByte = ord($data[0]);
    $secondByte = ord($data[1]);
    $payloadLen = $secondByte & 127;
    $mask = '';
    $offset = 2;

    if ($payloadLen === 126) {
        if ($length < 4) {
            return null;
        }
        $payloadLen = unpack('n', substr($data, 2, 2))[1];
        $offset = 4;
    } elseif ($payloadLen === 127) {
        if ($length < 10) {
            return null;
        }
        $parts = unpack('N2', substr($data, 2, 8));
        $payloadLen = ($parts[1] << 32) | $parts[2];
        $offset = 10;
    }

    if ($length < $offset + 4) {
        return null;
    }

    $mask = substr($data, $offset, 4);
    $offset += 4;
    $payload = substr($data, $offset, $payloadLen);

    $decoded = '';
    for ($i = 0; $i < $payloadLen; $i++) {
        $decoded .= $payload[$i] ^ $mask[$i % 4];
    }

    $opcode = $firstByte & 0x0F;
    // Accept only text frames (opcode 1) and continuation frames (0).
    if ($opcode !== 1 && $opcode !== 0) {
        return null;
    }

    return $decoded;
}

function encodeFrame(string $payload): string
{
    $frameHead = chr(0x81); // FIN + text frame
    $length = strlen($payload);

    if ($length <= 125) {
        $frameHead .= chr($length);
    } elseif ($length <= 65535) {
        $frameHead .= chr(126) . pack('n', $length);
    } else {
        $frameHead .= chr(127) . pack('NN', ($length >> 32) & 0xFFFFFFFF, $length & 0xFFFFFFFF);
    }

    return $frameHead . $payload;
}
