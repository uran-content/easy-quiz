<?php
require_once __DIR__ . '/../lib/helpers.php';

date_default_timezone_set('UTC');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Неверный запрос']);
    exit;
}

$quizId = $_POST['quiz_id'] ?? '';
$nickname = trim($_POST['nickname'] ?? '');
$realname = trim($_POST['realname'] ?? '');

if ($quizId === '') {
    echo json_encode(['error' => 'Квиз не найден']);
    exit;
}

$metadata = get_metadata($quizId);
if ($metadata === null) {
    echo json_encode(['error' => 'Квиз не найден']);
    exit;
}

if ($metadata['status'] === 'finished') {
    echo json_encode(['error' => 'Квиз уже завершён.']);
    exit;
}

if ($nickname === '') {
    echo json_encode(['error' => 'Введите никнейм.']);
    exit;
}

$participantId = uniqid('p_', true);
$participantsPath = participants_path($quizId);
$participants = load_json($participantsPath, []);

$participant = [
    'id' => $participantId,
    'nickname' => $nickname,
    'realname' => $realname,
    'joined_at' => date(DATE_ATOM),
];

$participants[$participantId] = $participant;
save_json($participantsPath, $participants);

setcookie('participant_' . $quizId, $participantId, time() + 60 * 60 * 24 * 7, '/');

$statusLabels = [
    'waiting' => 'Ожидание',
    'running' => 'Идёт',
    'finished' => 'Завершён',
];

echo json_encode([
    'participant' => $participant,
    'status' => $metadata['status'],
    'statusLabel' => $statusLabels[$metadata['status']] ?? $metadata['status'],
]);
