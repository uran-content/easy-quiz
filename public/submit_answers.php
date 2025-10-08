<?php
require_once __DIR__ . '/../lib/helpers.php';

date_default_timezone_set('UTC');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Неверный запрос']);
    exit;
}

$quizId = $_POST['quiz_id'] ?? '';
$participantId = $_POST['participant_id'] ?? '';
$answers = $_POST['answers'] ?? [];

if ($quizId === '' || $participantId === '') {
    echo json_encode(['error' => 'Отсутствуют данные участника.']);
    exit;
}

$metadata = get_metadata($quizId);
if ($metadata === null) {
    echo json_encode(['error' => 'Квиз не найден.']);
    exit;
}

if (($metadata['status'] ?? 'waiting') !== 'running') {
    echo json_encode(['error' => 'Отправка ответов сейчас недоступна.']);
    exit;
}

$participantsPath = participants_path($quizId);
$participants = load_json($participantsPath, []);
if (!isset($participants[$participantId])) {
    echo json_encode(['error' => 'Участник не найден.']);
    exit;
}

$responsesPath = responses_path($quizId);
$responses = load_json($responsesPath, []);
if (isset($responses[$participantId])) {
    echo json_encode(['error' => 'Ответы уже отправлены.']);
    exit;
}

$questions = $metadata['questions'] ?? [];
if (!is_array($answers)) {
    echo json_encode(['error' => 'Неверный формат ответов.']);
    exit;
}

$normalized = [];
$correctCount = 0;
foreach ($questions as $index => $question) {
    if (!isset($answers[$index])) {
        echo json_encode(['error' => 'Ответьте на все вопросы.']);
        exit;
    }
    $choiceIndex = (int)$answers[$index];
    if (!isset($question['choices'][$choiceIndex])) {
        echo json_encode(['error' => 'Обнаружен неверный вариант ответа.']);
        exit;
    }
    $normalized[$index] = $choiceIndex;
    if ((int)$question['answer'] === $choiceIndex) {
        $correctCount += 1;
    }
}

$responses[$participantId] = [
    'participant_id' => $participantId,
    'answers' => $normalized,
    'score' => $correctCount,
    'submitted_at' => date(DATE_ATOM),
];

save_json($responsesPath, $responses);

$message = sprintf('Ответы сохранены! Правильных ответов: %d из %d.', $correctCount, count($questions));

echo json_encode([
    'message' => $message,
    'score' => $correctCount,
    'total' => count($questions),
]);
