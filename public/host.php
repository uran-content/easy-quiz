<?php
require_once __DIR__ . '/../lib/helpers.php';

date_default_timezone_set('UTC');

$quizId = $_GET['id'] ?? '';
$metadata = require_metadata($quizId);

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$dirName = str_replace('\\', '/', dirname($scriptName));
$dirName = $dirName === '.' ? '' : $dirName;
$dirName = rtrim($dirName, '/');
$dirName = $dirName === '' ? '' : '/' . ltrim($dirName, '/');
$quizPath = $dirName . '/quiz.php';
$quizLink = $quizPath . '?id=' . urlencode($quizId);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$fullLink = rtrim($scheme . $host, '/') . $quizLink;
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($fullLink);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление квизом — <?php echo htmlspecialchars($metadata['title']); ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/host.css">
</head>
<body class="dashboard">
<header class="page-header">
    <h1><?php echo htmlspecialchars($metadata['title']); ?></h1>
    <p>Поделитесь QR-кодом или ссылкой со студентами. Когда будете готовы, начните квиз.</p>
</header>
<main class="host-layout">
    <section class="card host-controls">
        <h2>Ссылка для подключения</h2>
        <div class="share">
            <img src="<?php echo $qrUrl; ?>" alt="QR-код для студентов">
            <div class="share-details">
                <p><strong>Ссылка:</strong></p>
                <input type="text" readonly value="<?php echo htmlspecialchars($fullLink); ?>" onclick="this.select();">
                <button id="copyLink">Скопировать ссылку</button>
                <a class="button" href="<?php echo htmlspecialchars($quizLink); ?>" target="_blank" rel="noopener">Открыть страницу студента</a>
            </div>
        </div>
        <div class="status-panel">
            <p>Текущий статус: <span id="quizStatus" data-status="<?php echo htmlspecialchars($metadata['status']); ?>"><?php echo htmlspecialchars($metadata['status'] === 'waiting' ? 'Ожидание' : ($metadata['status'] === 'running' ? 'Идёт' : 'Завершён')); ?></span></p>
            <div class="control-buttons">
                <button id="startQuiz" <?php echo $metadata['status'] === 'running' ? 'disabled' : ''; ?>>Начать квиз</button>
                <button id="finishQuiz" class="secondary" <?php echo $metadata['status'] === 'finished' ? 'disabled' : ''; ?>>Завершить квиз</button>
                <a class="button secondary" href="export_csv.php?id=<?php echo urlencode($quizId); ?>">Экспорт CSV</a>
            </div>
        </div>
    </section>
    <section class="card stats-card">
        <h2>Статистика в реальном времени</h2>
        <div id="statsContainer" data-quiz="<?php echo htmlspecialchars($quizId); ?>">
            <p>Загрузка статистики...</p>
        </div>
    </section>
</main>
<script src="assets/host.js"></script>
</body>
</html>
