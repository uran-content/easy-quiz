const quizData = window.__QUIZ__ || {};
const pollInterval = 4000;
const wsConfig = window.__WS_CONFIG__ || {};

const select = (selector) => document.querySelector(selector);

const stateBlock = select('#quizState');
const joinCard = select('#joinCard');
const quizCard = select('#quizCard');
const joinForm = select('#joinForm');
const answersForm = select('#answersForm');
const questionsList = select('#questionsList');
const submissionResult = select('#submissionResult');

let currentStatus = quizData.status || 'waiting';
let participant = quizData.participant || null;
let submitted = participant?.submitted || false;
let pollTimer = null;
let questionsRendered = false;
let resultSocket = null;
const pendingMessages = [];

const statusMessages = {
    waiting: 'Ожидайте начала — преподаватель скоро запустит квиз.',
    running: 'Квиз открыт. Ответьте на вопросы и нажмите «Отправить ответы».',
    finished: 'Квиз завершён. Спасибо за участие!'
};

const renderQuestions = (answers = []) => {
    if (!questionsList) {
        return;
    }
    questionsList.innerHTML = '';
    quizData.questions.forEach((question, index) => {
        const block = document.createElement('div');
        block.className = 'question-item';

        const heading = document.createElement('h3');
        heading.textContent = `${index + 1}. ${question.text ?? ''}`;
        block.appendChild(heading);

        const answersWrapper = document.createElement('div');
        answersWrapper.className = 'answers';

        (question.choices || []).forEach((choice, choiceIndex) => {
            const id = `q${index}_choice${choiceIndex}`;
            const label = document.createElement('label');
            label.setAttribute('for', id);

            const input = document.createElement('input');
            input.type = 'radio';
            input.id = id;
            input.name = `answers[${index}]`;
            input.value = String(choiceIndex);
            input.required = true;
            if (answers[index] != null && Number(answers[index]) === choiceIndex) {
                input.checked = true;
            }

            const text = document.createElement('span');
            text.textContent = choice ?? '';

            label.appendChild(input);
            label.appendChild(text);
            answersWrapper.appendChild(label);
        });

        block.appendChild(answersWrapper);
        questionsList.appendChild(block);
    });
    questionsRendered = true;
};

const updateStateBlock = () => {
    stateBlock.textContent = statusMessages[currentStatus] || '';

    if (currentStatus === 'running' && participant && !submitted) {
        answersForm.hidden = false;
        submissionResult.hidden = true;
        if (!questionsRendered) {
            const existingAnswers = participant?.answers || [];
            renderQuestions(existingAnswers);
        }
    } else if (submitted) {
        answersForm.hidden = true;
        submissionResult.hidden = false;
        submissionResult.textContent = 'Ваши ответы отправлены. Ожидайте окончания квиза.';
    } else {
        answersForm.hidden = true;
        submissionResult.hidden = true;
        questionsRendered = false;
    }
};

const togglePolling = () => {
    if (pollTimer) {
        clearInterval(pollTimer);
    }
    pollTimer = setInterval(fetchStatus, pollInterval);
};

const buildWsUrl = () => {
    if (!wsConfig.domain || !wsConfig.path) {
        return null;
    }
    const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
    const domain = String(wsConfig.domain).replace(/^wss?:\/\//, '').replace(/\/$/, '');
    const path = `/${String(wsConfig.path).replace(/^\/+/, '')}`;
    return `${protocol}//${domain}${path}`;
};

const connectSocket = () => {
    if (resultSocket || !buildWsUrl()) {
        return;
    }
    try {
        resultSocket = new WebSocket(buildWsUrl());
        resultSocket.addEventListener('open', () => {
            while (pendingMessages.length > 0) {
                resultSocket.send(pendingMessages.shift());
            }
        });
        resultSocket.addEventListener('close', () => {
            resultSocket = null;
        });
        resultSocket.addEventListener('error', () => {
            resultSocket = null;
        });
    } catch (error) {
        resultSocket = null;
    }
};

const sendResultOverSocket = (payload) => {
    const url = buildWsUrl();
    if (!url) {
        return;
    }
    const message = JSON.stringify({
        type: 'quiz_result',
        quizId: quizData.id,
        participantId: participant?.id,
        nickname: participant?.nickname,
        realname: participant?.realname,
        payload,
    });

    if (!resultSocket || resultSocket.readyState === WebSocket.CLOSED) {
        connectSocket();
    }

    if (resultSocket && resultSocket.readyState === WebSocket.OPEN) {
        resultSocket.send(message);
    } else {
        pendingMessages.push(message);
    }
};

const collectAnswers = () => {
    const answers = [];
    if (!answersForm) {
        return answers;
    }
    quizData.questions.forEach((_, index) => {
        const input = answersForm.querySelector(`input[name="answers[${index}]"]:checked`);
        if (input) {
            answers[index] = Number(input.value);
        }
    });
    return answers;
};

const fetchStatus = () => {
    fetch(`get_quiz.php?id=${encodeURIComponent(quizData.id)}`)
        .then((response) => response.json())
        .then((data) => {
            if (!data || data.error) {
                return;
            }
            currentStatus = data.status;
            quizData.questions = data.questions;
            if (participant && data.participant && data.participant.submitted) {
                submitted = true;
            }
            if (data.status !== 'running') {
                questionsRendered = false;
            }
            updateStateBlock();
        })
        .catch(() => {});
};

if (joinForm) {
    joinForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(joinForm);
        fetch('join_quiz.php', {
            method: 'POST',
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                participant = data.participant;
                submitted = false;
                joinCard.hidden = true;
                quizCard.hidden = false;
                answersForm.reset();
                questionsRendered = false;
                currentStatus = data.status;
                updateStateBlock();
            })
            .catch(() => {
                alert('Не удалось подключиться. Попробуйте ещё раз.');
            });
    });
}

if (answersForm) {
    answersForm.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!participant) {
            alert('Сначала подключитесь к квизу.');
            return;
        }
        const formData = new FormData(answersForm);
        formData.append('quiz_id', quizData.id);
        formData.append('participant_id', participant.id);
        const selectedAnswers = collectAnswers();
        fetch('submit_answers.php', {
            method: 'POST',
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                submitted = true;
                submissionResult.hidden = false;
                submissionResult.textContent = data.message;
                answersForm.hidden = true;
                sendResultOverSocket({
                    answers: selectedAnswers,
                    score: data.score,
                    total: data.total,
                    submittedAt: new Date().toISOString(),
                });
            })
            .catch(() => {
                alert('Не удалось отправить ответы. Попробуйте ещё раз.');
            });
    });
}

if (participant) {
    quizCard.hidden = false;
    joinCard && (joinCard.hidden = true);
}

updateStateBlock();
togglePolling();
