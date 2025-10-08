<?php
require_once __DIR__ . '/../lib/helpers.php';

date_default_timezone_set('UTC');

header('Content-Type: application/json; charset=utf-8');

$quizId = $_GET['id'] ?? '';
if ($quizId === '') {
    echo json_encode(['error' => 'Квиз не найден']);
    exit;
}

$metadata = get_metadata($quizId);
if ($metadata === null) {
    echo json_encode(['error' => 'Квиз не найден']);
    exit;
}

$status = $metadata['status'] ?? 'waiting';
$statusLabels = [
    'waiting' => 'Ожидание',
    'running' => 'Идёт',
    'finished' => 'Завершён',
];

$participantInfo = null;
$participantKey = 'participant_' . $quizId;
$participantId = $_COOKIE[$participantKey] ?? '';
if ($participantId !== '') {
    $responses = load_json(responses_path($quizId), []);
    if (isset($responses[$participantId])) {
        $participantInfo = [
            'submitted' => true,
        ];
    }
}

echo json_encode([
    'id' => $quizId,
    'title' => $metadata['title'] ?? '',
    'description' => $metadata['description'] ?? '',
    'status' => $status,
    'statusLabel' => $statusLabels[$status] ?? $status,
    'questions' => $metadata['questions'] ?? [],
    'participant' => $participantInfo,
]);
