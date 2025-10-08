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

$participants = load_json(participants_path($quizId), []);
$responses = load_json(responses_path($quizId), []);
$questions = $metadata['questions'] ?? [];

$questionsStats = [];
foreach ($questions as $qIndex => $question) {
    $choicesStats = [];
    foreach ($question['choices'] as $cIndex => $choiceText) {
        $choicesStats[$cIndex] = [
            'text' => $choiceText,
            'count' => 0,
            'isCorrect' => ((int)$question['answer'] === $cIndex),
        ];
    }

    foreach ($responses as $response) {
        if (isset($response['answers'][$qIndex])) {
            $answerIndex = (int)$response['answers'][$qIndex];
            if (isset($choicesStats[$answerIndex])) {
                $choicesStats[$answerIndex]['count'] += 1;
            }
        }
    }

    $questionsStats[] = [
        'text' => $question['text'],
        'choices' => array_values($choicesStats),
    ];
}

$status = $metadata['status'] ?? 'waiting';
$statusLabels = [
    'waiting' => 'Ожидание',
    'running' => 'Идёт',
    'finished' => 'Завершён',
];

echo json_encode([
    'status' => $status,
    'statusLabel' => $statusLabels[$status] ?? $status,
    'participants' => count($participants),
    'responded' => count($responses),
    'questions' => $questionsStats,
]);
