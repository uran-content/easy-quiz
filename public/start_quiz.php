<?php
require_once __DIR__ . '/../lib/helpers.php';

date_default_timezone_set('UTC');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Неверный запрос']);
    exit;
}

$quizId = $_POST['id'] ?? '';
if ($quizId === '') {
    echo json_encode(['error' => 'Квиз не найден']);
    exit;
}

$metadata = get_metadata($quizId);
if ($metadata === null) {
    echo json_encode(['error' => 'Квиз не найден']);
    exit;
}

if (($metadata['status'] ?? 'waiting') === 'finished') {
    echo json_encode(['error' => 'Квиз уже завершён.']);
    exit;
}

$metadata['status'] = 'running';
write_metadata($quizId, $metadata);

$statusLabels = [
    'waiting' => 'Ожидание',
    'running' => 'Идёт',
    'finished' => 'Завершён',
];

echo json_encode([
    'status' => 'running',
    'statusLabel' => $statusLabels['running'],
]);
