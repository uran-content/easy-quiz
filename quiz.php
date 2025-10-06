<?php
require_once __DIR__ . '/lib/helpers.php';

default_timezone_set('UTC');

$quizId = $_GET['id'] ?? '';
$metadata = require_metadata($quizId);
$questionsJson = json_encode($metadata['questions'], JSON_UNESCAPED_UNICODE);
$participantKey = 'participant_' . $quizId;
$participantId = $_COOKIE[$participantKey] ?? '';
$participant = null;

if ($participantId !== '') {
    $participants = load_json(participants_path($quizId), []);
    if (isset($participants[$participantId])) {
        $participant = $participants[$participantId];
        $participant['id'] = $participantId;
        $responses = load_json(responses_path($quizId), []);
        if (isset($responses[$participantId])) {
            $participant['submitted'] = true;
            $participant['answers'] = $responses[$participantId]['answers'] ?? [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($metadata['title']); ?> — участие в квизе</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/quiz.css">
</head>
<body class="student">
<header class="page-header">
    <h1><?php echo htmlspecialchars($metadata['title']); ?></h1>
    <?php if (!empty($metadata['description'])): ?>
        <p><?php echo nl2br(htmlspecialchars($metadata['description'])); ?></p>
    <?php else: ?>
        <p>Введите никнейм, чтобы присоединиться к квизу. Ваши ответы остаются анонимными для одногруппников.</p>
    <?php endif; ?>
</header>
<main class="student-layout">
    <section class="card join-card" id="joinCard" <?php echo $participant ? 'hidden' : ''; ?>>
        <h2>Присоединиться</h2>
        <form id="joinForm">
            <input type="hidden" name="quiz_id" value="<?php echo htmlspecialchars($quizId); ?>">
            <div class="form-group">
                <label for="nickname">Отображаемый никнейм</label>
                <input type="text" id="nickname" name="nickname" required maxlength="40" placeholder="Например, Быстрый Ёж">
            </div>
            <div class="form-group">
                <label for="realname">Имя для отчёта (видно только преподавателю)</label>
                <input type="text" id="realname" name="realname" maxlength="80" placeholder="Например, Иван Иванов">
            </div>
            <button type="submit">Подключиться</button>
        </form>
    </section>
    <section class="card quiz-card" id="quizCard" data-status="<?php echo htmlspecialchars($metadata['status']); ?>" data-quiz="<?php echo htmlspecialchars($quizId); ?>" <?php echo $participant ? '' : 'hidden'; ?>>
        <div id="quizState" class="quiz-state"></div>
        <form id="answersForm" hidden>
            <div id="questionsList"></div>
            <button type="submit">Отправить ответы</button>
        </form>
        <div id="submissionResult" hidden></div>
    </section>
</main>
<script>
window.__QUIZ__ = {
    id: <?php echo json_encode($quizId, JSON_UNESCAPED_UNICODE); ?>,
    status: <?php echo json_encode($metadata['status'], JSON_UNESCAPED_UNICODE); ?>,
    questions: <?php echo $questionsJson; ?>,
    participant: <?php echo json_encode($participant, JSON_UNESCAPED_UNICODE); ?>
};
</script>
<script src="assets/quiz.js"></script>
</body>
</html>
