const quizData = window.__QUIZ__ || {};
const pollInterval = 4000;

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
        block.innerHTML = `<h3>${index + 1}. ${question.text}</h3>`;

        const answersWrapper = document.createElement('div');
        answersWrapper.className = 'answers';

        question.choices.forEach((choice, choiceIndex) => {
            const id = `q${index}_choice${choiceIndex}`;
            const label = document.createElement('label');
            label.setAttribute('for', id);
            const checked = answers[index] != null && Number(answers[index]) === choiceIndex ? 'checked' : '';
            label.innerHTML = `<input type="radio" id="${id}" name="answers[${index}]" value="${choiceIndex}" ${checked} required><span>${choice}</span>`;
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
