<?php
require_once __DIR__ . '/../lib/helpers.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Easy Quiz — интерактивные квизы за минуты</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="home">
<div class="background-pattern" aria-hidden="true"></div>
<header class="home-header">
    <div class="home-brand">Easy Quiz</div>
    <nav class="home-nav">
        <a href="#about">О сервисе</a>
        <a href="#steps">Инструкция</a>
        <a href="create.php" class="nav-cta">Создать квиз</a>
    </nav>
</header>
<main class="home-main">
    <section class="hero" id="about">
        <div class="hero-content">
            <span class="hero-kicker">Платформа для преподавателей и ведущих</span>
            <h1>Создавайте живые квизы, вовлекайте аудиторию и собирайте аналитику</h1>
            <p>Easy Quiz помогает проводить тесты, опросы и интерактивные занятия без сложных настроек. Соберите квиз, раздайте ссылку или QR-код и наблюдайте за результатами в реальном времени.</p>
            <div class="hero-actions">
                <a class="cta-button" href="create.php">Создать квиз</a>
                <a class="ghost-button" href="host.php" target="_blank" rel="noopener">Запустить существующий</a>
            </div>
            <ul class="hero-list">
                <li>Создание квиза занимает меньше 5 минут</li>
                <li>Студенты подключаются с любого устройства</li>
                <li>Результаты и экспорт в CSV сразу после завершения</li>
            </ul>
        </div>
        <div class="hero-visual">
            <div class="floating-card floating-card--primary">
                <img src="assets/quiz-illustration.svg" alt="Иллюстрация квиза">
            </div>
            <div class="floating-card floating-card--secondary">
                <img src="assets/team-illustration.svg" alt="Командная работа">
            </div>
            <div class="floating-bubble floating-bubble--one"></div>
            <div class="floating-bubble floating-bubble--two"></div>
        </div>
    </section>

    <section class="home-section highlights">
        <div class="section-header">
            <h2>Почему Easy Quiz?</h2>
            <p>Инструменты, которые экономят время и делают занятия динамичнее.</p>
        </div>
        <div class="highlight-grid">
            <article class="highlight-card">
                <h3>Гибкий конструктор</h3>
                <p>Добавляйте неограниченное количество вопросов и вариантов ответов, отмечайте правильные и управляйте структурой квиза.</p>
            </article>
            <article class="highlight-card">
                <h3>Мгновенный запуск</h3>
                <p>Участники подключаются по ссылке или QR-коду. Вы видите, кто готов, и запускаете квиз в пару кликов.</p>
            </article>
            <article class="highlight-card">
                <h3>Глубокая статистика</h3>
                <p>Следите за ответами в реальном времени, анализируйте сложные вопросы и экспортируйте результаты в CSV.</p>
            </article>
        </div>
    </section>

    <section class="home-section steps" id="steps">
        <div class="section-header">
            <h2>Инструкция по запуску</h2>
            <p>Следуйте этим шагам, чтобы провести яркий интерактивный квиз.</p>
        </div>
        <div class="steps-grid">
            <article class="step-card">
                <span class="step-number" aria-hidden="true">1</span>
                <div class="step-content">
                    <h3>Соберите квиз</h3>
                    <p>Перейдите в конструктор, придумайте название, опишите задание и добавьте вопросы с вариантами ответов.</p>
                </div>
            </article>
            <article class="step-card">
                <span class="step-number" aria-hidden="true">2</span>
                <div class="step-content">
                    <h3>Поделитесь ссылкой</h3>
                    <p>Получите уникальный QR-код и ссылку. Покажите их участникам — они подключатся с любого устройства и выберут никнейм.</p>
                </div>
            </article>
            <article class="step-card">
                <span class="step-number" aria-hidden="true">3</span>
                <div class="step-content">
                    <h3>Проведите и проанализируйте</h3>
                    <p>Запустите квиз, отслеживайте ответы в реальном времени, объявите победителей и выгрузите результаты в CSV.</p>
                </div>
            </article>
        </div>
    </section>

    <section class="home-section cta-section">
        <div class="cta-card">
            <h2>Готовы оживить своё занятие?</h2>
            <p>Нажмите на кнопку, чтобы перейти к созданию квиза. У вас всё получится — мы уже подготовили понятный конструктор.</p>
            <a class="cta-button" href="create.php">Создать квиз</a>
        </div>
    </section>
</main>
<footer class="home-footer">
    <p>Easy Quiz — ваш помощник в создании интерактивных занятий.</p>
</footer>
<script src="assets/home.js"></script>
</body>
</html>
