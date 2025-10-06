<?php
require_once __DIR__ . '/lib/helpers.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Easy Quiz - Создайте интерактивный квиз</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="page-header">
    <h1>Easy Quiz</h1>
    <p>Создайте интерактивный квиз и отслеживайте результаты в реальном времени.</p>
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
        <h3>Как это работает</h3>
        <ol>
            <li>Создайте квиз и получите QR-код и ссылку для студентов.</li>
            <li>В день лекции покажите QR-код — студенты подключатся и выберут никнеймы.</li>
            <li>Начните квиз, отслеживайте ответы в реальном времени и экспортируйте результаты в CSV.</li>
        </ol>
    </section>
</main>
<script src="assets/index.js"></script>
</body>
</html>
