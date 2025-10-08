<?php
require_once __DIR__ . '/../lib/helpers.php';

date_default_timezone_set('UTC');

$quizId = $_GET['id'] ?? '';
if ($quizId === '') {
    http_response_code(404);
    echo 'Квиз не найден';
    exit;
}

$metadata = get_metadata($quizId);
if ($metadata === null) {
    http_response_code(404);
    echo 'Квиз не найден';
    exit;
}

$participants = load_json(participants_path($quizId), []);
$responses = load_json(responses_path($quizId), []);
$questions = $metadata['questions'] ?? [];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $quizId . '.csv"');

echo "\xEF\xBB\xBF"; // UTF-8 BOM

$out = fopen('php://output', 'w');
$header = ['ID участника', 'Имя для отчёта', 'Никнейм', 'Вопрос', 'Вариант участника', 'Правильный ответ', 'Верно', 'Дата отправки'];
fputcsv($out, $header, ';');

foreach ($responses as $participantId => $response) {
    $participant = $participants[$participantId] ?? [];
    $nickname = $participant['nickname'] ?? '';
    $realname = $participant['realname'] ?? '';
    $submittedAt = $response['submitted_at'] ?? '';

    foreach ($questions as $index => $question) {
        $answerIndex = $response['answers'][$index] ?? null;
        $answerText = ($answerIndex !== null && isset($question['choices'][$answerIndex])) ? $question['choices'][$answerIndex] : '';
        $correctIndex = (int)($question['answer'] ?? -1);
        $correctText = isset($question['choices'][$correctIndex]) ? $question['choices'][$correctIndex] : '';
        $isCorrect = ($answerIndex !== null && $correctIndex === (int)$answerIndex) ? 'Да' : 'Нет';

        $row = [
            $participantId,
            $realname,
            $nickname,
            ($index + 1) . '. ' . ($question['text'] ?? ''),
            $answerText,
            $correctText,
            $isCorrect,
            $submittedAt,
        ];
        fputcsv($out, $row, ';');
    }
}

fclose($out);
exit;
