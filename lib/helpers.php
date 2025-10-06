<?php

declare(strict_types=1);

function data_base_path(): string
{
    return __DIR__ . '/../data/quizzes';
}

function quiz_dir(string $quizId): string
{
    $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '', $quizId);
    return data_base_path() . '/' . $safeId;
}

function load_json(string $path, $default = [])
{
    if (!file_exists($path)) {
        return $default;
    }

    $content = file_get_contents($path);
    if ($content === false || $content === '') {
        return $default;
    }

    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : $default;
}

function save_json(string $path, $data): void
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($path, $json, LOCK_EX);
}

function get_metadata(string $quizId): ?array
{
    $metaPath = quiz_dir($quizId) . '/metadata.json';
    if (!file_exists($metaPath)) {
        return null;
    }

    return load_json($metaPath, null);
}

function require_metadata(string $quizId): array
{
    $metadata = get_metadata($quizId);
    if ($metadata === null) {
        http_response_code(404);
        echo 'Квиз не найден.';
        exit;
    }

    return $metadata;
}

function write_metadata(string $quizId, array $metadata): void
{
    $metaPath = quiz_dir($quizId) . '/metadata.json';
    save_json($metaPath, $metadata);
}

function participants_path(string $quizId): string
{
    return quiz_dir($quizId) . '/participants.json';
}

function responses_path(string $quizId): string
{
    return quiz_dir($quizId) . '/responses.json';
}
