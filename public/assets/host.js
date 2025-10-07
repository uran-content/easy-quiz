const pollInterval = 3000;

const escapeHtml = (value) =>
    String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

const toSafeNumber = (value) => {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : 0;
};

document.addEventListener('DOMContentLoaded', () => {
    const statsContainer = document.getElementById('statsContainer');
    const quizId = statsContainer.dataset.quiz;
    const startBtn = document.getElementById('startQuiz');
    const finishBtn = document.getElementById('finishQuiz');
    const statusLabel = document.getElementById('quizStatus');
    const copyLinkBtn = document.getElementById('copyLink');

    const renderStats = (data) => {
        if (!data || !data.questions) {
            statsContainer.innerHTML = '<p>Нет данных для отображения.</p>';
            return;
        }

        const participantsCount = toSafeNumber(data.participants);
        const respondedCount = toSafeNumber(data.responded);
        const summary = `Участников: <strong>${participantsCount}</strong> · Ответы получены: <strong>${respondedCount}</strong>`;
        const blocks = data.questions.map((question, idx) => {
            const items = question.choices.map((choice) => {
                const badgeClass = choice.isCorrect ? 'badge correct' : 'badge';
                const liClass = choice.isCorrect ? 'correct' : '';
                const choiceCount = toSafeNumber(choice.count);
                return `<li class="${liClass}"><span class="label">${escapeHtml(choice.text)}</span><span class="${badgeClass}">${choiceCount}</span></li>`;
            }).join('');
            return `<div class="stats-question"><h3>${idx + 1}. ${escapeHtml(question.text)}</h3><ul>${items}</ul></div>`;
        }).join('');

        statsContainer.innerHTML = `<div class="stats-summary">${summary}</div>${blocks}`;
    };

    const refreshStats = () => {
        fetch(`stats.php?id=${encodeURIComponent(quizId)}`)
            .then((response) => response.json())
            .then((data) => {
                renderStats(data);
                if (data.status) {
                    statusLabel.textContent = data.statusLabel;
                    statusLabel.dataset.status = data.status;
                }
            })
            .catch(() => {
                statsContainer.innerHTML = '<p>Не удалось загрузить статистику.</p>';
            });
    };

    const postAction = (endpoint) => {
        const formData = new FormData();
        formData.append('id', quizId);
        return fetch(endpoint, {
            method: 'POST',
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                statusLabel.textContent = data.statusLabel;
                statusLabel.dataset.status = data.status;
                startBtn.disabled = data.status === 'running';
                finishBtn.disabled = data.status === 'finished';
                refreshStats();
            })
            .catch(() => {
                alert('Не удалось выполнить действие. Попробуйте ещё раз.');
            });
    };

    startBtn?.addEventListener('click', () => postAction('start_quiz.php'));
    finishBtn?.addEventListener('click', () => postAction('finish_quiz.php'));

    if (copyLinkBtn) {
        copyLinkBtn.addEventListener('click', async () => {
            const input = copyLinkBtn.previousElementSibling;
            try {
                await navigator.clipboard.writeText(input.value);
                copyLinkBtn.textContent = 'Скопировано!';
                setTimeout(() => {
                    copyLinkBtn.textContent = 'Скопировать ссылку';
                }, 2000);
            } catch (error) {
                input.select();
                document.execCommand('copy');
                copyLinkBtn.textContent = 'Скопировано!';
                setTimeout(() => {
                    copyLinkBtn.textContent = 'Скопировать ссылку';
                }, 2000);
            }
        });
    }

    refreshStats();
    setInterval(refreshStats, pollInterval);
});
