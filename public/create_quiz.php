<?php
require_once __DIR__ . '/../lib/helpers.php';

date_default_timezone_set('UTC');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create.php');
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$questionsRaw = $_POST['questions'] ?? [];

$errors = [];
$questions = [];

if ($title === '') {
    $errors[] = 'Название квиза обязательно.';
}

if (!is_array($questionsRaw) || count($questionsRaw) === 0) {
    $errors[] = 'Добавьте хотя бы один вопрос.';
}

foreach ($questionsRaw as $index => $questionData) {
    $text = trim($questionData['text'] ?? '');
    $choicesRaw = $questionData['choices'] ?? [];
    $answerIndexRaw = isset($questionData['answer']) ? (int)$questionData['answer'] : -1;

    if ($text === '') {
        $errors[] = "Вопрос №" . ($index + 1) . " не заполнен.";
        continue;
    }

    if (!is_array($choicesRaw)) {
        $errors[] = "У вопроса №" . ($index + 1) . " нет вариантов ответов.";
        continue;
    }

    $choices = [];
    $correctIndex = null;
    foreach ($choicesRaw as $choiceIdx => $choiceText) {
        $choiceText = trim((string)$choiceText);
        if ($choiceText === '') {
            continue;
        }
        if ($choiceIdx === $answerIndexRaw) {
            $correctIndex = count($choices);
        }
        $choices[] = $choiceText;
    }

    if (count($choices) < 2) {
        $errors[] = "У вопроса №" . ($index + 1) . " должно быть минимум два варианта ответа.";
        continue;
    }

    if ($correctIndex === null) {
        $correctIndex = 0;
    }

    $questions[] = [
        'text' => $text,
        'choices' => $choices,
        'answer' => $correctIndex,
    ];
}

if (!empty($errors)) {
    $message = implode('<br>', array_map('htmlspecialchars', $errors, array_fill(0, count($errors), ENT_QUOTES)));
    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Ошибка создания</title>';
    echo '<link rel="stylesheet" href="assets/style.css"></head><body class="error-page">';
    echo '<div class="error-card">';
    echo '<h1>Не удалось сохранить квиз</h1>';
    echo '<p>' . $message . '</p>';
    echo '<a class="button" href="create.php">Вернуться и исправить</a>';
    echo '</div></body></html>';
    exit;
}

$quizId = uniqid('quiz_', true);
$quizDir = quiz_dir($quizId);
if (!is_dir($quizDir)) {
    mkdir($quizDir, 0777, true);
}

$metadata = [
    'id' => $quizId,
    'title' => $title,
    'description' => $description,
    'questions' => $questions,
    'status' => 'waiting',
    'created_at' => date(DATE_ATOM),
];

save_json($quizDir . '/metadata.json', $metadata);
save_json($quizDir . '/participants.json', []);
save_json($quizDir . '/responses.json', []);

header('Location: host.php?id=' . urlencode($quizId));
exit;
