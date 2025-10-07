<?php
require_once __DIR__ . '/../lib/helpers.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Easy Quiz — Конструктор квиза</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="app-body">
<header class="page-header">
    <h1>Конструктор квиза</h1>
    <p>Опишите квиз, добавьте вопросы и мгновенно получите ссылки и QR-код для участников.</p>
</header>
<main class="container">
    <section class="card">
        <h2>Создание нового квиза</h2>
        <form action="create_quiz.php" method="post" id="createQuizForm">
            <div class="form-group">
                <label for="title">Название квиза</label>
                <input type="text" id="title" name="title" required placeholder="Например, Итоговый тест по теме 3">
            </div>
            <div class="form-group">
                <label for="description">Описание / инструкции</label>
                <textarea id="description" name="description" rows="3" placeholder="Добавьте пояснения для студентов"></textarea>
            </div>
            <div id="questionsContainer"></div>
            <div class="form-actions">
                <button type="button" class="secondary" id="addQuestion">Добавить вопрос</button>
                <button type="submit">Сохранить и получить ссылку</button>
            </div>
        </form>
    </section>
    <section class="card info-card">
        <h3>Подсказки по созданию</h3>
        <ul class="tips-list">
            <li>Формулируйте вопросы коротко и по делу.</li>
            <li>Используйте описание, чтобы дать контекст участникам.</li>
            <li>Проверьте правильные ответы перед запуском квиза.</li>
        </ul>
        <a class="back-link" href="index.php">← Вернуться на главную</a>
    </section>
</main>
<script src="assets/index.js"></script>
</body>
</html>
